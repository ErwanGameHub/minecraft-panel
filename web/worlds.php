<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (isset($_GET['download_backup']) && $_GET['download_backup'] === 'latest') {
    $files = glob(BACKUP_DIR . '/bedrock_world_*.tar.gz') ?: [];
    rsort($files);
    $file = $files[0] ?? null;
    if (!$file || !is_readable($file)) {
        http_response_code(404);
        exit('No backup found.');
    }
    while (ob_get_level() > 0) ob_end_clean();
    if (headers_sent()) exit('Download aborted: output already started.');
    ini_set('zlib.output_compression', '0');
    set_time_limit(0);
    session_write_close();
    header('Cache-Control: no-store');
    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

require_once __DIR__ . '/includes/layout.php';
page_header('Worlds & Backups', 'worlds');
?>
<div class="grid">
    <section class="card wide">
        <h2 class="icon-heading"><?= panel_icon('upload') ?>Upload & Restore World</h2>
        <p class="muted">Upload `.zip` or `.tar.gz` backups. Existing matching worlds are backed up before restoration.</p>
        <div id="dropZone">Drop a world backup here, or click to choose a file</div>
        <input id="worldFile" type="file" accept=".zip,.tar.gz" hidden>
        <div class="file-selected" id="selectedFile"></div>
        <button id="uploadBtn" class="button-success with-icon" type="button"><?= panel_icon('upload') ?>Upload & Restore</button>
        <div class="progress"><span id="uploadBar"></span></div>
        <pre class="console terminal-small" id="uploadLog">Ready for a world backup.</pre>
    </section>
    <section class="card">
        <h2 class="icon-heading"><?= panel_icon('archive') ?>Backup Tools</h2>
        <p class="muted">Create a fresh backup before large changes or download your latest archived world.</p>
        <div class="backup-actions">
            <form method="post"><button name="action" value="backup" class="button-success with-icon"><?= panel_icon('archive') ?>Create Backup Now</button></form>
            <a class="button button-success download with-icon" href="?download_backup=latest" data-download><?= panel_icon('download') ?>Download Latest Backup</a>
        </div>
    </section>
</div>
<?php page_footer(); ?>
