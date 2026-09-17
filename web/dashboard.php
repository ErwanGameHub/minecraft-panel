<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';

$online = server_online();
$maxPlayers = server_property('max-players', '10');
page_header('Dashboard', 'dashboard');
?>
<div class="dashboard-grid">
    <section class="card dashboard-panel dashboard-health-page">
        <div class="panel-heading">
            <h2><?= panel_icon('dashboard', 'heading-icon') ?>Server Health</h2>
            <span class="health-pill <?= $online ? 'good-pill' : 'bad-pill' ?>"><?= $online ? 'Online' : 'Offline' ?></span>
        </div>
        <div class="health-grid">
            <div class="health-tile player-tile">
                <?= panel_icon('users', 'health-icon player-icon') ?>
                <strong class="player-count"><span data-player-online>...</span><span class="player-divider">/</span><span data-player-max><?= htmlspecialchars($maxPlayers) ?></span></strong>
                <span>Players</span>
            </div>
            <div class="health-tile">
                <?= panel_icon('gauge', 'health-icon') ?>
                <strong data-cpu-usage-copy>...</strong>
                <span>System CPU</span>
            </div>
            <div class="health-tile">
                <?= panel_icon('network', 'health-icon') ?>
                <strong><?= htmlspecialchars((string)server_port()) ?></strong>
                <span>Port</span>
            </div>
            <div class="health-tile">
                <?= panel_icon('activity', 'health-icon') ?>
                <strong data-cpu-load-primary>...</strong>
                <span>Load Average</span>
            </div>
        </div>
        <form method="post" class="controls dashboard-controls">
            <button name="action" value="start" class="button-success with-icon" title="Start server"><?= panel_icon('play') ?>Start</button>
            <button name="action" value="restart" class="button-secondary with-icon" title="Restart server"><?= panel_icon('refresh') ?>Restart</button>
            <button name="action" value="stop" class="button-danger with-icon" title="Stop server"><?= panel_icon('stop') ?>Stop</button>
        </form>
    </section>
</div>
<?php page_footer(); ?>
