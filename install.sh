#!/bin/bash
set -e

GITHUB_REPO="${GITHUB_REPO:-https://github.com/ErwanGameHub/minecraft-panel}"
PHP_VERSION="8.4"
PANEL_DOMAIN="${PANEL_DOMAIN:-}"
PANEL_EMAIL="${PANEL_EMAIL:-}"
PANEL_IP_HTTPS="${PANEL_IP_HTTPS:-0}"
PANEL_SOURCE_DIR="${PANEL_SOURCE_DIR:-}"
PANEL_ADMIN_USER="${PANEL_ADMIN_USER:-admin}"
PANEL_ADMIN_PASSWORD="${PANEL_ADMIN_PASSWORD:-}"

if [ -n "$PANEL_DOMAIN" ] && ! [[ "$PANEL_DOMAIN" =~ ^[A-Za-z0-9.-]+$ ]]; then
    echo "Error: PANEL_DOMAIN contains invalid characters."
    exit 1
fi

if [ -r /etc/os-release ]; then
    . /etc/os-release
else
    echo "Error: Cannot detect the operating system."
    exit 1
fi

case "$ID:$VERSION_CODENAME" in
    debian:bookworm|debian:trixie|ubuntu:jammy|ubuntu:noble)
        ;;
    *)
        echo "Error: Supported systems are Debian 12/13 and Ubuntu 22.04/24.04."
        echo "Detected: ${PRETTY_NAME:-$ID $VERSION_ID}"
        exit 1
        ;;
esac

echo "============================================"
echo " Minecraft Panel Full Installer"
echo "============================================"

echo "[1/10] Updating system..."
apt update -y && apt upgrade -y

echo "[2/10] Installing dependencies..."
apt install -y lsb-release ca-certificates gnupg2 curl sudo git screen ufw unzip tar wget cron python3 openssl

IP=$(curl -4s https://api.ipify.org || hostname -I | awk '{print $1}')

if ! command -v nginx &>/dev/null; then
    echo "[2a/10] Installing Nginx..."
    apt install -y nginx
fi

echo "[3/10] Installing PHP $PHP_VERSION..."
if ! command -v "php$PHP_VERSION" &>/dev/null; then
    if ! apt-cache show "php$PHP_VERSION-fpm" &>/dev/null; then
        if [ "$ID" = "debian" ]; then
            curl -fsSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb
            dpkg -i /tmp/debsuryorg-archive-keyring.deb
            echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $VERSION_CODENAME main" > /etc/apt/sources.list.d/php.list
        else
            apt install -y software-properties-common
            LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php
        fi
        apt update -y
    fi
    apt install -y php$PHP_VERSION php$PHP_VERSION-fpm php$PHP_VERSION-cli php$PHP_VERSION-curl php$PHP_VERSION-zip php$PHP_VERSION-mbstring
else
    apt install -y php$PHP_VERSION-fpm php$PHP_VERSION-curl php$PHP_VERSION-zip php$PHP_VERSION-mbstring
fi

echo "[4/10] Creating minecraft user..."
id minecraft &>/dev/null || useradd -m -s /bin/bash minecraft

echo "[5/10] Installing panel..."
rm -rf /tmp/minecraft-panel
if [ -n "$PANEL_SOURCE_DIR" ]; then
    cp -r "$PANEL_SOURCE_DIR" /tmp/minecraft-panel
else
    git clone "$GITHUB_REPO" /tmp/minecraft-panel
fi

rm -rf /usr/local/share/minecraft-panel
mkdir -p /usr/local/share/minecraft-panel
cp -a /tmp/minecraft-panel/. /usr/local/share/minecraft-panel/

rm -rf /var/www/*
cp -r /tmp/minecraft-panel/web/* /var/www/
chown -R www-data:www-data /var/www
chmod -R 755 /var/www

echo "[6/10] Creating config.php..."
cat <<'EOF' > /var/www/config.php
<?php
$panel_version = "1.0";
$server_dir = "/home/minecraft/Server";
$backup_dir = "/home/minecraft/BackupWorlds";
EOF
chown www-data:www-data /var/www/config.php
chmod 644 /var/www/config.php

echo "[7/10] Installing server scripts..."
mkdir -p /home/minecraft/Server/worlds
mkdir -p /home/minecraft/BackupWorlds
cp -r /tmp/minecraft-panel/scripts/* /home/minecraft/
chown -R minecraft:minecraft /home/minecraft
chmod +x /home/minecraft/*.sh
chmod 755 /home/minecraft/panel_accounts.php

echo "[7a/10] Configuring panel accounts and terminal menu..."
mkdir -p /var/lib/minecraft-panel
chown root:www-data /var/lib/minecraft-panel
chmod 2750 /var/lib/minecraft-panel
if [ ! -s /var/lib/minecraft-panel/users.json ]; then
    if [ -z "$PANEL_ADMIN_PASSWORD" ]; then
        PANEL_ADMIN_PASSWORD=$(openssl rand -base64 24 | tr -d '/+=' | head -c 20)
    fi
    printf '%s' "$PANEL_ADMIN_PASSWORD" | php /home/minecraft/panel_accounts.php create "$PANEL_ADMIN_USER"
    INITIAL_ACCOUNT_CREATED=1
else
    INITIAL_ACCOUNT_CREATED=0
fi
install -o root -g root -m 755 /home/minecraft/menu.sh /usr/local/bin/menu
install -o root -g root -m 755 /home/minecraft/uninstall_panel.sh /usr/local/sbin/minecraft-panel-uninstall
install -o root -g root -m 755 /home/minecraft/reinstall_panel.sh /usr/local/sbin/minecraft-panel-reinstall

if [ ! -x /home/minecraft/Server/bedrock_server ]; then
    echo "[7b/10] Installing Minecraft Bedrock server..."
    bash /home/minecraft/install_Server.sh
fi

echo "[7c/10] Applying Bedrock performance defaults..."
bash /home/minecraft/optimize_Server.sh

echo "[7d/10] Ensuring swap memory is available..."
bash /home/minecraft/ensure_swap.sh

echo "[7e/10] Scheduling daily Minecraft restart at 12:00 UTC..."
cat > /etc/cron.d/minecraft-panel-restart <<EOF
SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
# Checked hourly so daily_restart.sh can enforce 12:00 UTC regardless of host timezone.
0 * * * * www-data /home/minecraft/daily_restart.sh
EOF
chmod 644 /etc/cron.d/minecraft-panel-restart
systemctl enable --now cron

touch /home/minecraft/minecraft.log
chown minecraft:www-data /home/minecraft/minecraft.log
chmod 664 /home/minecraft/minecraft.log

echo "[8/10] Setting sudo permissions..."
cat > /etc/sudoers.d/minecraft-panel <<EOF
www-data ALL=(minecraft) NOPASSWD: /home/minecraft/*.sh
EOF
chmod 440 /etc/sudoers.d/minecraft-panel

echo "[9/10] Configuring Nginx..."
cp /tmp/minecraft-panel/nginx/minecraft-panel.conf /etc/nginx/sites-available/minecraft-panel
sed -i 's|root .*;|root /var/www;|' /etc/nginx/sites-available/minecraft-panel
sed -i "s|__DOMAIN__|${PANEL_DOMAIN:-_}|" /etc/nginx/sites-available/minecraft-panel
ln -sf /etc/nginx/sites-available/minecraft-panel /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

PHP_INI="/etc/php/$PHP_VERSION/fpm/php.ini"

sed -i 's/^upload_max_filesize.*/upload_max_filesize = 50G/' $PHP_INI || true
sed -i 's/^post_max_size.*/post_max_size = 50G/' $PHP_INI || true
sed -i 's/^memory_limit.*/memory_limit = 1024M/' $PHP_INI || true
sed -i 's/^max_execution_time.*/max_execution_time = 0/' $PHP_INI || true
sed -i 's/^max_input_time.*/max_input_time = 0/' $PHP_INI || true

systemctl restart php$PHP_VERSION-fpm
systemctl restart nginx

echo "[10/10] Configuring firewall..."
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 19132/tcp
ufw allow 19132/udp
ufw allow 19133/udp
ufw --force enable

if [ -n "$PANEL_DOMAIN" ]; then
    echo "[SSL] Enabling HTTPS for $PANEL_DOMAIN..."
    apt install -y certbot python3-certbot-nginx
    if [ -n "$PANEL_EMAIL" ]; then
        certbot --nginx --non-interactive --agree-tos --redirect -m "$PANEL_EMAIL" -d "$PANEL_DOMAIN"
    else
        certbot --nginx --non-interactive --agree-tos --redirect --register-unsafely-without-email -d "$PANEL_DOMAIN"
    fi
elif [ "$PANEL_IP_HTTPS" = "1" ]; then
    echo "[SSL] Enabling short-lived HTTPS certificate for $IP..."
    bash /home/minecraft/enable_ip_https.sh "$IP" "$PANEL_EMAIL"
fi

echo "[FIX] Applying final permissions..."
chown -R www-data:www-data /home/minecraft
chown root:root /home/minecraft/*.sh
chmod 755 /home/minecraft/*.sh
chown root:root /home/minecraft/panel_accounts.php
chmod 755 /home/minecraft/panel_accounts.php

if [ -f /home/minecraft/Server/bedrock_server ]; then
    chmod +x /home/minecraft/Server/bedrock_server
fi

cd /home/minecraft/Server
touch minecraft.log
chown minecraft:minecraft minecraft.log
chmod 644 minecraft.log
chmod 755 /home/minecraft/Server

if [ -x /home/minecraft/Server/bedrock_server ]; then
    echo "[START] Starting Minecraft Bedrock server..."
    runuser -u www-data -- /home/minecraft/manage_screen.sh start
fi

echo ""
echo "============================================"
echo " INSTALL COMPLETE!"
echo "============================================"
if [ "${INITIAL_ACCOUNT_CREATED:-0}" = "1" ]; then
    echo " Login username: $PANEL_ADMIN_USER"
    echo " Login password: $PANEL_ADMIN_PASSWORD"
    echo " Change accounts later by running: sudo menu"
fi
if [ -n "$PANEL_DOMAIN" ]; then
    echo " Panel: https://$PANEL_DOMAIN"
elif [ "$PANEL_IP_HTTPS" = "1" ]; then
    echo " Panel: https://$IP"
else
    echo " Panel: http://$IP"
    echo " HTTPS: set PANEL_DOMAIN to a DNS name pointed at this server when installing."
fi
echo "============================================"
