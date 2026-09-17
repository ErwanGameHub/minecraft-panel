<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';
$properties = @file_get_contents(SERVER_DIR . '/server.properties') ?: "# server.properties not found\n";
page_header('Configuration', 'settings');
?>
<div class="grid">
    <section class="card full">
        <h2 class="icon-heading"><?= panel_icon('settings') ?>server.properties</h2>
        <p class="muted">Changing `server-port` changes the displayed server address after saving. Restart the server to apply runtime changes.</p>
        <form method="post">
            <textarea name="properties" spellcheck="false"><?= htmlspecialchars($properties) ?></textarea>
            <div class="controls">
                <button name="action" value="save_properties" class="button-success with-icon"><?= panel_icon('save') ?>Save Configuration</button>
                <button name="action" value="restart" class="button-secondary with-icon"><?= panel_icon('refresh') ?>Restart Server</button>
            </div>
        </form>
    </section>
</div>
<?php page_footer(); ?>
