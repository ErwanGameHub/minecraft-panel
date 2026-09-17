#!/bin/bash
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "Run as root: sudo /usr/local/sbin/minecraft-panel-uninstall --yes"
    exit 1
fi

if [ "${1:-}" != "--yes" ]; then
    echo "This permanently removes the panel, Minecraft worlds, backups, accounts, and panel services."
    echo "Run again with --yes to continue."
    exit 1
fi

echo "Stopping Minecraft and web services..."
if [ -x /home/minecraft/manage_screen.sh ]; then
    runuser -u www-data -- /home/minecraft/manage_screen.sh stop >/dev/null 2>&1 || true
fi
systemctl disable --now minecraft-bedrock-motd-proxy.service >/dev/null 2>&1 || true
systemctl disable --now nginx >/dev/null 2>&1 || true
systemctl disable --now php8.4-fpm >/dev/null 2>&1 || true

if command -v ufw >/dev/null 2>&1; then
    ufw --force disable >/dev/null 2>&1 || true
fi

echo "Removing panel files and configuration..."
rm -rf -- /home/minecraft/Server /home/minecraft/BackupWorlds
rm -f -- /home/minecraft/*.sh /home/minecraft/*.php /home/minecraft/*.log /home/minecraft/.panel-public-port
rm -rf -- /var/lib/minecraft-panel
rm -rf -- /var/www/*
rm -f -- /etc/cron.d/minecraft-panel-restart /etc/sudoers.d/minecraft-panel
rm -f -- /etc/nginx/sites-enabled/minecraft-panel /etc/nginx/sites-available/minecraft-panel
rm -f -- /etc/systemd/system/minecraft-bedrock-motd-proxy.service
rm -rf -- /etc/systemd/system/minecraft-bedrock-motd-proxy.service.d
systemctl daemon-reload >/dev/null 2>&1 || true
rm -rf -- /home/minecraft
userdel minecraft >/dev/null 2>&1 || true

echo "Removing web stack and panel-specific packages..."
DEBIAN_FRONTEND=noninteractive apt-get purge -y \
    'nginx*' 'php8.4*' certbot python3-certbot-nginx screen ufw debsuryorg-archive-keyring >/dev/null 2>&1 || true
DEBIAN_FRONTEND=noninteractive apt-get autoremove -y --purge >/dev/null 2>&1 || true
apt-get clean >/dev/null 2>&1 || true

rm -f -- /etc/apt/sources.list.d/php.list
rm -f -- /etc/apt/sources.list.d/ondrej-ubuntu-php*.list
rm -f -- /usr/share/keyrings/deb.sury.org-php.gpg
rm -rf -- /etc/letsencrypt /var/lib/letsencrypt /var/log/letsencrypt
rm -rf -- /usr/local/share/minecraft-panel
rm -f -- /usr/local/bin/menu /usr/local/sbin/minecraft-panel-uninstall /usr/local/sbin/minecraft-panel-reinstall

echo "Minecraft Panel has been completely removed. SSH remains available."
