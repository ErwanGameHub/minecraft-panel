#!/bin/bash
set -e
SCREEN_NAME="bedrock"
SERVER_DIR="/home/minecraft/Server"
LOG_FILE="/home/minecraft/minecraft.log"
online() { screen -ls | grep -qE '[0-9]+\.bedrock[[:space:]]'; }
start() {
    exec 9>/home/minecraft/.world-maintenance.lock
    flock -n 9 || { echo 'World maintenance in progress'; return 1; }
    if online; then echo 'Server already running'; return; fi
    test -x "$SERVER_DIR/bedrock_server" || { echo 'Server binary missing'; return 1; }
    cd "$SERVER_DIR"
    screen -dmS "$SCREEN_NAME" bash -c 'exec env LD_LIBRARY_PATH=. ./bedrock_server >> /home/minecraft/minecraft.log 2>&1' 9>&-
    sleep 5
    online || { echo "Server startup failed; check $LOG_FILE"; return 1; }
    echo 'Server process started; check the log for gameplay readiness.'
}
stop() {
    if ! online; then echo 'Server already stopped'; return; fi
    screen -S "$SCREEN_NAME" -p 0 -X stuff $'stop\r'
    for ((attempt=0; attempt<60; attempt++)); do
        if ! online; then echo 'Server stopped'; return; fi
        sleep 1
    done
    echo 'Server did not stop cleanly; operation cancelled.'
    return 1
}
case "${1:-}" in
    start) start ;;
    stop) stop ;;
    restart) stop; start ;;
    status) if online; then echo Online; else echo Offline; fi ;;
    *) echo 'Usage: manage_screen.sh {start|stop|restart|status}'; exit 1 ;;
esac
