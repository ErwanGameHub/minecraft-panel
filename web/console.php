<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';
page_header('Console', 'console');
?>
<div class="grid">
    <section class="card full">
        <h2 class="icon-heading"><?= panel_icon('console') ?>Live Server Console</h2>
        <pre class="console" data-live-console>Loading console...</pre>
        <form method="post" class="row">
            <input name="server_command" aria-label="Server command" placeholder="Server command" maxlength="512" required>
            <input type="hidden" name="console_token" value="<?= htmlspecialchars($_SESSION['console_token']) ?>">
            <button name="action" value="server_command" class="button-success with-icon"><?= panel_icon('send') ?>Execute</button>
        </form>
        <form method="post" class="controls" style="margin-top:12px">
            <button name="action" value="clear_console" class="button-secondary with-icon"><?= panel_icon('trash') ?>Clear Console Display</button>
            <button name="action" value="restart" class="button-secondary with-icon"><?= panel_icon('refresh') ?>Restart Server</button>
        </form>
    </section>
    <section class="card half">
        <h2 class="icon-heading"><?= panel_icon('send') ?>Broadcast Message</h2>
        <form method="post" class="row">
            <input name="command" placeholder="Message to online players" required>
            <button name="action" value="command" class="button-success with-icon"><?= panel_icon('send') ?>Send</button>
        </form>
    </section>
    <section class="card half">
        <h2 class="icon-heading"><?= panel_icon('gamepad') ?>Gamerule</h2>
        <form method="post" class="row">
            <select name="gamerule"><?php foreach ($gamerules as $rule): ?><option><?= htmlspecialchars($rule) ?></option><?php endforeach; ?></select>
            <input name="value" placeholder="true / false / number" required>
            <button name="action" value="gamerule" class="button-success with-icon"><?= panel_icon('save') ?>Apply</button>
        </form>
    </section>
</div>
<?php page_footer(); ?>
