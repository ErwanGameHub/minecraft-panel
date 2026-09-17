<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (!isset($_SESSION['logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}
session_write_close();
$lines = @file('/proc/stat', FILE_IGNORE_NEW_LINES);
if (!$lines || !preg_match('/^cpu\s+(.+)$/', $lines[0], $match)) {
    http_response_code(503);
    echo json_encode(['error' => 'CPU metrics unavailable']);
    exit;
}
$fields = preg_split('/\s+/', trim($match[1]));
if (count($fields) < 8) {
    http_response_code(503);
    echo json_encode(['error' => 'CPU metrics unavailable']);
    exit;
}
// Guest counters are already included in user/nice; do not count them twice.
$ticks = array_map('floatval', array_slice($fields, 0, 8));
$cores = 0;
foreach ($lines as $line) {
    if (preg_match('/^cpu\d+\s/', $line)) $cores++;
}
echo json_encode([
    'total' => array_sum($ticks),
    'idle' => $ticks[3] + $ticks[4],
    'cores' => $cores,
    'load' => sys_getloadavg() ?: null,
]);
