<?php
session_start();
header('Content-Type: text/plain; charset=utf-8');
if (!isset($_SESSION['logged_in'])) {
    http_response_code(403);
    exit('Authentication required');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('POST required');
}
$name = $_POST['name'] ?? '';
if (!is_string($name) || basename($name) !== $name || !preg_match('/\.(zip|tar\.gz)$/i', $name)) {
    http_response_code(400);
    exit('Invalid backup filename');
}
set_time_limit(0);
ini_set('display_errors', '0');
$lock = fopen('/home/minecraft/.world-maintenance.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
    http_response_code(409);
    exit('Another world operation is running.');
}
exec('/home/minecraft/manage_screen.sh status', $status, $code);
if ($code !== 0 || trim(implode("\n", $status)) !== 'Offline') {
    http_response_code(409);
    exit('Stop the Minecraft server before restoring.');
}
$output = [];
exec('python3 ' . escapeshellarg(__DIR__ . '/includes/restore_world.py') . ' ' .
    escapeshellarg(__DIR__ . '/uploads/' . $name) . ' 2>&1', $output, $code);
http_response_code($code === 0 ? 200 : 500);
echo implode("\n", $output);
