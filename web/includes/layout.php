<?php
function panel_icon(string $name, string $class = 'ui-icon'): string {
    $icons = [
        'overview' => '<rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect>',
        'dashboard' => '<path d="M4 17h16"></path><path d="M6 17a6 6 0 0 1 12 0"></path><path d="m12 11 3-4"></path><circle cx="12" cy="17" r="1"></circle>',
        'console' => '<rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="m7 9 3 3-3 3M13 15h4"></path>',
        'worlds' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path><path d="m3.3 7 8.7 5 8.7-5M12 22V12"></path>',
        'power' => '<path d="M12 2v10"></path><path d="M18.4 6.6a9 9 0 1 1-12.8 0"></path>',
        'settings' => '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"></path>',
        'management' => '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.1h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H3v-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3A1.7 1.7 0 0 0 10 3h4a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.1v4H21a1.7 1.7 0 0 0-1.6 1Z"></path>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path>',
        'refresh' => '<path d="M20 11a8 8 0 1 0-2.3 5.7"></path><path d="M20 4v7h-7"></path>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"></path>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path><path d="M17 21v-8H7v8M7 3v5h8"></path>',
        'upload' => '<path d="M12 16V4M7 9l5-5 5 5"></path><path d="M20 16v4H4v-4"></path>',
        'download' => '<path d="M12 4v12M7 11l5 5 5-5"></path><path d="M20 20H4"></path>',
        'archive' => '<path d="M4 7h16v14H4zM3 3h18v4H3zM9 11h6"></path>',
        'server' => '<rect width="16" height="6" x="4" y="4" rx="2"></rect><rect width="16" height="6" x="4" y="14" rx="2"></rect><path d="M8 7h.01M8 17h.01"></path>',
        'gamepad' => '<path d="M6 11h4M8 9v4M15 12h.01M18 10h.01"></path><path d="M17.3 5H6.7A4.7 4.7 0 0 0 2 9.7v4.6A4.7 4.7 0 0 0 6.7 19c1.4 0 2.7-.6 3.6-1.6h3.4A4.7 4.7 0 0 0 22 14.3V9.7A4.7 4.7 0 0 0 17.3 5Z"></path>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"></path>',
        'gauge' => '<path d="M4 17h16M6 17a6 6 0 0 1 12 0M12 11l3-4"></path><circle cx="12" cy="17" r="1"></circle>',
        'network' => '<path d="M12 2v20M8 6l4-4 4 4M8 18l4 4 4-4"></path>',
        'activity' => '<path d="M3 12h4l2-6 4 12 2-6h6"></path>',
        'play' => '<path d="m8 5 11 7-11 7Z"></path>',
        'stop' => '<rect x="7" y="7" width="10" height="10" rx="1"></rect>',
    ];
    $body = $icons[$name] ?? $icons['settings'];
    return '<svg class="' . htmlspecialchars($class) . '" viewBox="0 0 24 24" aria-hidden="true">' . $body . '</svg>';
}

function page_header(string $title, string $active): void {
    $address = server_address();
    $online = server_online();
    $navigation = [
        'overview' => ['index.php', 'Overview', 'Server summary', 'overview'],
        'dashboard' => ['dashboard.php', 'Dashboard', 'Health and controls', 'dashboard'],
        'console' => ['console.php', 'Console', 'Live commands', 'console'],
        'worlds' => ['worlds.php', 'Worlds', 'Backups and restore', 'worlds'],
        'startup' => ['startup.php', 'Startup', 'Launch settings', 'power'],
        'settings' => ['settings.php', 'Configuration', 'Gameplay options', 'settings'],
        'management' => ['management.php', 'Management', 'Updates and tools', 'management']
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> | Minecraft Panel</title>
<link rel="stylesheet" href="assets/panel.css?v=16">
<script defer src="assets/panel.js?v=16"></script>
</head>
<body>
<header class="topbar">
    <button class="menu-toggle" type="button" data-menu-toggle>Menu</button>
    <div class="brand"><span class="brand-mark">M</span><div>Minecraft Panel<small>Bedrock Hosting</small></div></div>
    <div class="top-actions">
        <button class="theme-toggle button-secondary" type="button" data-theme-toggle aria-label="Switch to light theme" title="Switch to light theme">
            <svg class="theme-icon theme-icon-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M19.1 4.9L17 7M7 17l-2.1 2.1"></path></svg>
            <svg class="theme-icon theme-icon-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 15.2A8.8 8.8 0 0 1 8.8 3.5 9 9 0 1 0 20.5 15.2Z"></path></svg>
        </button>
        <div class="server-chip"><span class="dot <?= $online ? '' : 'offline' ?>"></span><?= htmlspecialchars($address) ?></div>
    </div>
</header>
<aside class="sidebar" data-sidebar>
    <nav>
    <?php foreach ($navigation as $key => [$url, $label, $subtitle, $icon]): ?>
        <a class="<?= $key === $active ? 'active' : '' ?>" href="<?= $url ?>">
            <?= panel_icon($icon, 'nav-icon') ?>
            <span class="nav-copy"><strong><?= $label ?></strong><small><?= $subtitle ?></small></span>
        </a>
    <?php endforeach; ?>
    </nav>
    <a class="logout" href="?logout=1">Sign out</a>
</aside>
<main class="page">
    <div class="page-title"><div><h1><?= panel_icon($navigation[$active][3] ?? 'settings', 'page-title-icon') ?><?= htmlspecialchars($title) ?></h1><p>Manage your Minecraft Bedrock server securely.</p></div>
    <button class="copy-button button-secondary" type="button" data-copy="<?= htmlspecialchars($address) ?>" aria-label="Copy server address">
        <svg class="button-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 8h10v12H9z"></path><path d="M5 16H4V4h10v1"></path></svg>
        <span><?= htmlspecialchars($address) ?></span>
    </button></div>
    <div class="toast-stack" data-toast-stack aria-live="polite">
    <?php global $message, $messageType; if ($message !== ''): ?>
        <div class="toast toast-<?= $messageType === 'error' ? 'error' : 'success' ?>" data-toast>
            <span><?= nl2br(htmlspecialchars($message)) ?></span>
            <button class="toast-close" type="button" data-toast-close aria-label="Close notification">&times;</button>
        </div>
    <?php endif; ?>
    </div>
<?php
}

function page_footer(): void {
?>
</main>
</body>
</html>
<?php
}
