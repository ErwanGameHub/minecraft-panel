#!/bin/bash
set -euo pipefail
ROOT_DIR=/home/minecraft
SERVER_DIR="$ROOT_DIR/Server"
BACKUP_DIR="$ROOT_DIR/BackupWorlds"
mkdir -p "$BACKUP_DIR"
exec 9>"$ROOT_DIR/.world-maintenance.lock"
flock -n 9 || { echo 'World maintenance in progress'; exit 1; }
WORLD_NAME=$(sed -n 's/^level-name=//p' "$SERVER_DIR/server.properties" | head -n 1 | tr -d '\r')
WORLD_NAME=${WORLD_NAME:-Bedrock level}
case "$WORLD_NAME" in */*|*\\*|.|..) echo 'Invalid world name'; exit 1 ;; esac
test -f "$SERVER_DIR/worlds/$WORLD_NAME/level.dat"
RESTART=0
PARTIAL=''
cleanup() {
    result=$?
    trap - EXIT
    if [[ -n "$PARTIAL" ]]; then rm -f -- "$PARTIAL"; fi
    flock -u 9
    if [[ "$RESTART" == 1 ]]; then
        "$ROOT_DIR/manage_screen.sh" start || result=1
    fi
    exit "$result"
}
trap cleanup EXIT
if [[ $("$ROOT_DIR/manage_screen.sh" status) == Online ]]; then
    RESTART=1
    "$ROOT_DIR/manage_screen.sh" stop
fi
PARTIAL=$(mktemp "$BACKUP_DIR/.backup-XXXXXX")
tar -czf "$PARTIAL" -C "$SERVER_DIR/worlds" -- "$WORLD_NAME"
gzip -t "$PARTIAL"
tar -tzf "$PARTIAL" >/dev/null
FINAL="$BACKUP_DIR/bedrock_world_$(date +%F_%H-%M-%S)_$$.tar.gz"
mv -- "$PARTIAL" "$FINAL"
PARTIAL=''
echo "Backup verified: $FINAL"
echo 'Previous backups retained.'
