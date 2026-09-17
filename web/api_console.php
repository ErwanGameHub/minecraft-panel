<?php
require_once __DIR__ . '/includes/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
echo "=== Live Console Log ===\n";
if (!is_readable(LOG_FILE)) {
    echo 'Log file not found.';
    exit;
}

$lines = @file(LOG_FILE, FILE_IGNORE_NEW_LINES) ?: [];
$visible = [];
$skipPlayerNames = 0;
foreach ($lines as $line) {
    if (preg_match('/There are\s+(\d+)\/\d+\s+players online:/i', $line, $matches)) {
        $skipPlayerNames = (int)$matches[1];
        continue;
    }
    if ($skipPlayerNames > 0 && !preg_match('/^\[\d{4}-\d{2}-\d{2}/', $line)) {
        $skipPlayerNames--;
        continue;
    }
    if ($line === '' && $visible !== [] && end($visible) === '') {
        continue;
    }
    $visible[] = $line;
}
echo implode("\n", array_slice($visible, -80));
