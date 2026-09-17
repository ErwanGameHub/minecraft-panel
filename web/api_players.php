<?php
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
session_write_close();

function player_limit(): int {
    $value = server_property('max-players', '10');
    return max(1, (int)$value);
}

function latest_player_count_from_log(): ?int {
    $text = (string)shell_exec('tail -n 120 ' . escapeshellarg(LOG_FILE) . ' 2>/dev/null');
    $lines = $text === '' ? [] : preg_split('/\r?\n/', trim($text));
    foreach (array_reverse($lines ?: []) as $line) {
        if (preg_match('/There are\s+(\d+)\s*\/\s*(\d+)\s+players online/i', $line, $matches)) {
            return (int)$matches[1];
        }
    }
    return null;
}

$maxPlayers = player_limit();
$online = server_online();
$count = null;

if ($online) {
    $cacheFile = '/tmp/minecraft-panel-players.json';
    $cached = is_readable($cacheFile) ? json_decode((string)file_get_contents($cacheFile), true) : null;
    if (is_array($cached) && time() - (int)($cached['time'] ?? 0) < 10) {
        $count = is_numeric($cached['online'] ?? null) ? (int)$cached['online'] : null;
    } else {
        exec('screen -S bedrock -p 0 -X stuff ' . escapeshellarg("list\r"), $output, $code);
        usleep(350000);
        $count = latest_player_count_from_log();
        if ($count !== null) {
            @file_put_contents($cacheFile, json_encode(['time' => time(), 'online' => $count]), LOCK_EX);
        }
    }
}

echo json_encode([
    'online' => $online,
    'players' => $count,
    'max' => $maxPlayers,
]);
