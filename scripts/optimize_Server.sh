#!/bin/bash
set -e

PROPERTIES="/home/minecraft/Server/server.properties"
CPU_THREADS=$(nproc)

# Retire the temporary RakNet compatibility proxy used by older panel builds.
if systemctl list-unit-files minecraft-bedrock-motd-proxy.service >/dev/null 2>&1; then
    systemctl disable --now minecraft-bedrock-motd-proxy.service >/dev/null 2>&1 || true
fi
rm -f /home/minecraft/.panel-public-port

mkdir -p "$(dirname "$PROPERTIES")"
touch "$PROPERTIES"

set_property() {
    local key="$1"
    local value="$2"
    if grep -q "^${key}=" "$PROPERTIES"; then
        sed -i "s/^${key}=.*/${key}=${value}/" "$PROPERTIES"
    else
        printf '%s=%s\n' "$key" "$value" >> "$PROPERTIES"
    fi
}

# Conservative defaults for smaller VPS hosts: lower chunk transmission cost while retaining playability.
set_property server-port 19132
set_property server-portv6 19133
set_property server-ip ""
set_property server-udp-ports 19132
set_property view-distance 16
set_property tick-distance 4
set_property max-threads "$CPU_THREADS"
set_property allow-list false
set_property online-mode true
set_property transport nethernet
set_property enable-lan-visibility false

echo "Applied Bedrock defaults: server-port=19132, server-udp-ports=19132, transport=nethernet, online-mode=true, allow-list=false, enable-lan-visibility=false, view-distance=16, tick-distance=4, max-threads=$CPU_THREADS."
