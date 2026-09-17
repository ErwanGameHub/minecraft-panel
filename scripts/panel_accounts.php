#!/usr/bin/env php
<?php
declare(strict_types=1);

const ACCOUNT_FILE = '/var/lib/minecraft-panel/users.json';

function fail(string $message): never {
    fwrite(STDERR, "Error: $message\n");
    exit(1);
}

function load_accounts(): array {
    if (!is_readable(ACCOUNT_FILE)) {
        return [];
    }
    $accounts = json_decode((string)file_get_contents(ACCOUNT_FILE), true);
    return is_array($accounts) ? $accounts : [];
}

function save_accounts(array $accounts): void {
    $directory = dirname(ACCOUNT_FILE);
    if (!is_dir($directory) && !mkdir($directory, 0750, true)) {
        fail('Unable to create the account directory.');
    }
    ksort($accounts);
    $temporary = ACCOUNT_FILE . '.tmp';
    $json = json_encode($accounts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($temporary, $json . "\n", LOCK_EX) === false) {
        fail('Unable to write the account file.');
    }
    chmod($temporary, 0640);
    if (function_exists('posix_getgrnam')) {
        $group = posix_getgrnam('www-data');
        if (is_array($group)) {
            chown($temporary, 0);
            chgrp($temporary, (int)$group['gid']);
        }
    }
    if (!rename($temporary, ACCOUNT_FILE)) {
        @unlink($temporary);
        fail('Unable to install the account file.');
    }
}

function validate_username(string $username): void {
    if (!preg_match('/^[A-Za-z0-9_.-]{3,32}$/', $username)) {
        fail('Username must be 3-32 characters using letters, numbers, dot, dash, or underscore.');
    }
}

function validate_password(string $password): void {
    if (strlen($password) < 8 || strlen($password) > 128) {
        fail('Password must be between 8 and 128 characters.');
    }
}

$command = $argv[1] ?? '';
$username = trim((string)($argv[2] ?? ''));
$accounts = load_accounts();

switch ($command) {
    case 'create':
        $password = isset($argv[3]) ? (string)$argv[3] : (string)stream_get_contents(STDIN);
        validate_username($username);
        validate_password($password);
        if (isset($accounts[$username])) {
            fail("Account '$username' already exists.");
        }
        $accounts[$username] = password_hash($password, PASSWORD_DEFAULT);
        save_accounts($accounts);
        echo "Account '$username' created.\n";
        break;
    case 'delete':
        validate_username($username);
        if (!isset($accounts[$username])) {
            fail("Account '$username' does not exist.");
        }
        if (count($accounts) <= 1) {
            fail('The final panel account cannot be deleted.');
        }
        unset($accounts[$username]);
        save_accounts($accounts);
        echo "Account '$username' deleted.\n";
        break;
    case 'password':
        $password = isset($argv[3]) ? (string)$argv[3] : (string)stream_get_contents(STDIN);
        validate_username($username);
        validate_password($password);
        if (!isset($accounts[$username])) {
            fail("Account '$username' does not exist.");
        }
        $accounts[$username] = password_hash($password, PASSWORD_DEFAULT);
        save_accounts($accounts);
        echo "Password changed for '$username'.\n";
        break;
    case 'list':
        echo $accounts === [] ? "No accounts configured.\n" : implode("\n", array_keys($accounts)) . "\n";
        break;
    default:
        fail('Usage: panel_accounts.php create|delete|password|list [username] [password]');
}
