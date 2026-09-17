<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';

$online = server_online();
$installed = installed_version();
$memory = memory_usage();
$diskTotal = (float)disk_total_space('/');
$diskUsed = $diskTotal - (float)disk_free_space('/');
$diskPercent = $diskTotal > 0 ? min(100, round(($diskUsed / $diskTotal) * 100, 1)) : 0;
page_header('Overview', 'overview');
?>
<div class="dashboard-grid">
    <section class="card dashboard-panel status-panel">
        <div class="panel-heading">
            <h2><svg class="heading-icon" viewBox="0 0 24 24" aria-hidden="true"><rect width="16" height="6" x="4" y="4" rx="2"></rect><rect width="16" height="6" x="4" y="14" rx="2"></rect><path d="M8 7h.01M8 17h.01"></path></svg>Server Status</h2>
            <span class="health-pill <?= $online ? 'good-pill' : 'bad-pill' ?>"><?= $online ? 'Running' : 'Offline' ?></span>
        </div>
        <div class="mini-grid">
            <div class="metric-tile">
                <svg class="tile-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 6v6l4 2"></path><circle cx="12" cy="12" r="9"></circle></svg>
                <strong><?= htmlspecialchars(uptime_text()) ?></strong>
                <span>Uptime</span>
            </div>
            <div class="metric-tile">
                <svg class="tile-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 17V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"></path><path d="M8 9h8M8 13h5"></path></svg>
                <strong><?= htmlspecialchars((string)getmypid()) ?></strong>
                <span>Panel PID</span>
            </div>
            <div class="metric-tile">
                <svg class="tile-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="5" width="14" height="14" rx="2"></rect><path d="M9 9h6v6H9zM9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"></path></svg>
                <strong><?= htmlspecialchars($installed ?? 'Unknown') ?></strong>
                <span>Bedrock</span>
            </div>
            <div class="metric-tile address-tile" title="<?= htmlspecialchars(server_ipv4()) ?>">
                <svg class="tile-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"></path></svg>
                <strong><?= htmlspecialchars(server_ipv4()) ?></strong>
                <span>Server IP</span>
            </div>
        </div>
    </section>

    <section class="card dashboard-panel resources-panel">
        <div class="panel-heading">
            <h2><svg class="heading-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2"></rect><path d="M9 9h6v6H9zM9 1v3M15 1v3M9 20v3M15 20v3M1 9h3M1 15h3M20 9h3M20 15h3"></path></svg>System Resources</h2>
            <span class="health-pill">Live</span>
        </div>
        <div class="resource-row">
            <span class="resource-label"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="5" width="14" height="14" rx="2"></rect><path d="M9 9h6v6H9z"></path></svg>CPU Usage</span>
            <strong data-cpu-usage>Measuring...</strong>
        </div>
        <meter class="resource-meter" data-cpu-meter min="0" max="100" value="0" aria-label="VPS CPU usage" hidden></meter>
        <div class="resource-row">
            <span class="resource-label"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 19V5M10 19V5M14 19V5M18 19V5M4 7h16M4 17h16"></path></svg>Memory</span>
            <strong><?= htmlspecialchars(memory_text()) ?></strong>
        </div>
        <meter class="resource-meter" min="0" max="100" value="<?= htmlspecialchars((string)$memory['percent']) ?>" aria-label="VPS memory usage"></meter>
        <div class="resource-row">
            <span class="resource-label"><svg viewBox="0 0 24 24" aria-hidden="true"><ellipse cx="12" cy="5" rx="8" ry="3"></ellipse><path d="M4 5v7c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12v7c0 1.7 3.6 3 8 3s8-1.3 8-3v-7"></path></svg>Storage</span>
            <strong><?= htmlspecialchars(format_bytes($diskUsed)) ?> / <?= htmlspecialchars(format_bytes($diskTotal)) ?></strong>
        </div>
        <meter class="resource-meter" min="0" max="100" value="<?= htmlspecialchars((string)$diskPercent) ?>" aria-label="VPS storage usage"></meter>
        <ul class="metrics compact-metrics">
            <li><span>Logical cores</span><strong data-cpu-cores>...</strong></li>
            <li><span>Load average</span><strong data-cpu-load>...</strong></li>
        </ul>
    </section>

</div>
<?php page_footer(); ?>
