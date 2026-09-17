document.addEventListener('DOMContentLoaded', function () {
    const root = document.documentElement;
    const themeButton = document.querySelector('[data-theme-toggle]');
    const toastStack = document.querySelector('[data-toast-stack]');
    const dismissToast = function (toast) {
        if (toast) toast.remove();
    };
    const startToastTimer = function (toast) {
        window.setTimeout(function () { dismissToast(toast); }, 5000);
    };
    const showToast = function (text, type) {
        if (!toastStack) return;
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + (type === 'error' ? 'error' : 'success');
        toast.setAttribute('data-toast', '');
        const message = document.createElement('span');
        message.textContent = text;
        const close = document.createElement('button');
        close.className = 'toast-close';
        close.type = 'button';
        close.setAttribute('aria-label', 'Close notification');
        close.innerHTML = '&times;';
        close.addEventListener('click', function () { dismissToast(toast); });
        toast.appendChild(message);
        toast.appendChild(close);
        toastStack.appendChild(toast);
        startToastTimer(toast);
    };
    document.querySelectorAll('[data-toast]').forEach(function (toast) {
        const close = toast.querySelector('[data-toast-close]');
        if (close) close.addEventListener('click', function () { dismissToast(toast); });
        startToastTimer(toast);
    });
    const savedTheme = window.localStorage.getItem('panel-theme') || 'dark';
    const applyTheme = function (theme) {
        root.dataset.theme = theme;
        if (themeButton) {
            const nextTheme = theme === 'dark' ? 'light' : 'dark';
            const label = 'Switch to ' + nextTheme + ' theme';
            themeButton.setAttribute('aria-label', label);
            themeButton.setAttribute('title', label);
        }
    };
    applyTheme(savedTheme);
    if (themeButton) {
        themeButton.addEventListener('click', function () {
            const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            window.localStorage.setItem('panel-theme', next);
            applyTheme(next);
        });
    }

    document.querySelectorAll('.switch input').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const state = toggle.parentElement.querySelector('.switch-state');
            if (state) state.textContent = toggle.checked ? 'Enabled' : 'Disabled';
        });
    });

    const menu = document.querySelector('[data-sidebar]');
    const toggle = document.querySelector('[data-menu-toggle]');
    if (toggle && menu) {
        toggle.addEventListener('click', function () { menu.classList.toggle('open'); });
    }

    document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            const text = button.dataset.copy;
            const fallbackCopy = function () {
                const input = document.createElement('textarea');
                input.value = text;
                input.setAttribute('readonly', '');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                const copied = document.execCommand('copy');
                input.remove();
                if (copied) showToast('Server address copied.', 'success');
                else showToast('Unable to copy server address.', 'error');
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function () {
                    showToast('Server address copied.', 'success');
                }).catch(fallbackCopy);
            } else {
                fallbackCopy();
            }
        });
    });
    document.querySelectorAll('[data-download]').forEach(function (link) {
        link.addEventListener('click', function () {
            showToast('Backup download started.', 'success');
        });
    });

    const cpuUsage = document.querySelector('[data-cpu-usage]');
    const cpuCopy = document.querySelector('[data-cpu-usage-copy]');
    const loadPrimary = document.querySelector('[data-cpu-load-primary]');
    if (cpuUsage || cpuCopy || loadPrimary) {
        const meter = document.querySelector('[data-cpu-meter]');
        const cores = document.querySelector('[data-cpu-cores]');
        const load = document.querySelector('[data-cpu-load]');
        let previous = null;
        const timeoutSignal = function (milliseconds) {
            if (window.AbortSignal && AbortSignal.timeout) return AbortSignal.timeout(milliseconds);
            const controller = new AbortController();
            window.setTimeout(function () { controller.abort(); }, milliseconds);
            return controller.signal;
        };
        const loadCpu = async function () {
            if (!document.hidden) {
                try {
                    const response = await fetch('api_cpu.php', {
                        cache: 'no-store', signal: timeoutSignal(8000)
                    });
                    if (!response.ok) throw new Error('CPU metrics unavailable');
                    const current = await response.json();
                    if (![current.total, current.idle, current.cores].every(Number.isFinite)) {
                        throw new Error('Invalid CPU metrics');
                    }
                    const total = previous ? current.total - previous.total : 0;
                    const idle = previous ? current.idle - previous.idle : 0;
                    if (total > 0 && idle >= 0 && idle <= total) {
                        const usage = Math.max(0, Math.min(100, 100 * (total - idle) / total));
                        const usageText = usage.toFixed(1) + '%';
                        if (cpuUsage) cpuUsage.textContent = usageText;
                        if (cpuCopy) cpuCopy.textContent = usageText;
                        if (meter) {
                            meter.value = usage;
                            meter.hidden = false;
                        }
                    } else {
                        if (cpuUsage) cpuUsage.textContent = 'Measuring...';
                        if (cpuCopy) cpuCopy.textContent = '...';
                        if (meter) meter.hidden = true;
                    }
                    if (cores) cores.textContent = current.cores;
                    if (load && Array.isArray(current.load)) {
                        const loadValues = current.load.map(function (value) {
                            return Number(value).toFixed(2);
                        });
                        load.textContent = loadValues.join(' / ');
                        if (loadPrimary) loadPrimary.textContent = loadValues[0];
                    }
                    previous = current;
                } catch (error) {
                    if (cpuUsage) cpuUsage.textContent = 'Unavailable';
                    if (meter) meter.hidden = true;
                    if (cores) cores.textContent = 'Unavailable';
                    if (load) load.textContent = 'Unavailable';
                    if (cpuCopy) cpuCopy.textContent = 'Unavailable';
                    if (loadPrimary) loadPrimary.textContent = 'Unavailable';
                    previous = null;
                }
            } else {
                previous = null;
            }
            window.setTimeout(loadCpu, 5000);
        };
        loadCpu();
    }

    const playerOnline = document.querySelector('[data-player-online]');
    if (playerOnline) {
        const playerMax = document.querySelector('[data-player-max]');
        const loadPlayers = function () {
            fetch('api_players.php', {cache: 'no-store'}).then(function (response) {
                if (!response.ok) throw new Error('Player metrics unavailable');
                return response.json();
            }).then(function (data) {
                playerOnline.textContent = Number.isFinite(data.players) ? data.players : (data.online ? '0' : '0');
                if (playerMax && Number.isFinite(data.max)) playerMax.textContent = data.max;
            }).catch(function () {
                playerOnline.textContent = '0';
            }).finally(function () {
                window.setTimeout(loadPlayers, 15000);
            });
        };
        loadPlayers();
    }

    const consoleBox = document.querySelector('[data-live-console]');
    if (consoleBox) {
        const loadConsole = function () {
            fetch('api_console.php').then(function (response) { return response.text(); }).then(function (text) {
                consoleBox.textContent = text;
                consoleBox.scrollTop = consoleBox.scrollHeight;
            });
        };
        loadConsole();
        window.setInterval(loadConsole, 3000);
    }

    const fileInput = document.getElementById('worldFile');
    const dropZone = document.getElementById('dropZone');
    if (!fileInput || !dropZone) return;

    const selected = document.getElementById('selectedFile');
    const bar = document.getElementById('uploadBar');
    const log = document.getElementById('uploadLog');
    const button = document.getElementById('uploadBtn');
    const showFile = function () {
        const file = fileInput.files[0];
        selected.textContent = file ? 'Selected: ' + file.name + ' (' + Math.ceil(file.size / 1024 / 1024) + ' MB)' : '';
    };
    const writeLog = function (text) {
        if (log.textContent === 'Ready for a world backup.') log.textContent = '';
        log.textContent += text + '\n';
        log.scrollTop = log.scrollHeight;
    };
    const requestText = async function (url, options) {
        const response = await fetch(url, options);
        const text = await response.text();
        if (!response.ok) throw new Error(text || 'HTTP ' + response.status);
        return text;
    };

    dropZone.addEventListener('click', function () { fileInput.click(); });
    dropZone.addEventListener('dragover', function (event) { event.preventDefault(); dropZone.classList.add('drag'); });
    dropZone.addEventListener('dragleave', function () { dropZone.classList.remove('drag'); });
    dropZone.addEventListener('drop', function (event) {
        event.preventDefault();
        dropZone.classList.remove('drag');
        fileInput.files = event.dataTransfer.files;
        showFile();
    });
    fileInput.addEventListener('change', showFile);
    button.addEventListener('click', async function () {
        const file = fileInput.files[0];
        if (!file) {
            writeLog('Choose a backup file first.');
            return showToast('Choose a backup file first.', 'error');
        }
        if (!/\.(zip|tar\.gz)$/i.test(file.name)) {
            writeLog('Only .zip and .tar.gz files are supported.');
            return showToast('Unsupported backup file type.', 'error');
        }
        const size = 5 * 1024 * 1024;
        const total = Math.ceil(file.size / size);
        log.textContent = '';
        button.disabled = true;
        try {
            for (let index = 0; index < total; index++) {
                const form = new FormData();
                form.append('chunk', file.slice(index * size, (index + 1) * size));
                form.append('name', file.name);
                form.append('index', index);
                form.append('total', total);
                writeLog(await requestText('upload.php', {method: 'POST', body: form}));
                bar.style.width = Math.floor(((index + 1) / total) * 70) + '%';
            }
            writeLog(await requestText('merge.php?name=' + encodeURIComponent(file.name) + '&total=' + total));
            bar.style.width = '84%';
            writeLog(await requestText('restore.php', {
                method: 'POST', body: new URLSearchParams({name: file.name})
            }));
            bar.style.width = '100%';
            writeLog('Restore complete.');
            showToast('World uploaded and restored successfully.', 'success');
        } catch (error) {
            writeLog('Upload stopped: ' + error.message);
            showToast('Upload or restore failed.', 'error');
        } finally {
            button.disabled = false;
        }
    });
});
