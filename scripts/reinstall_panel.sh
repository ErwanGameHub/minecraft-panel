#!/bin/bash
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "Run as root: sudo /usr/local/sbin/minecraft-panel-reinstall"
    exit 1
fi

repository="${GITHUB_REPO:-https://github.com/ErwanGameHub/minecraft-panel}"
current_domain=""
if [ -r /etc/nginx/sites-available/minecraft-panel ]; then
    current_domain=$(sed -n 's/^[[:space:]]*server_name[[:space:]]\+\([^ ;]*\).*/\1/p' /etc/nginx/sites-available/minecraft-panel | grep -v '^_$' | head -1 || true)
fi

read -r -p "Panel domain [${current_domain:-none}]: " panel_domain
panel_domain="${panel_domain:-$current_domain}"
read -r -p "Certificate email (optional): " panel_email
read -r -p "New panel username [admin]: " panel_user
panel_user="${panel_user:-admin}"
read -r -s -p "New panel password (leave blank to generate): " panel_password
echo
if [ -n "$panel_password" ]; then
    read -r -s -p "Confirm password: " confirmation
    echo
    if [ "$panel_password" != "$confirmation" ]; then
        echo "Passwords do not match."
        exit 1
    fi
fi

temporary_root=$(mktemp -d)
temporary_source="$temporary_root/source"
trap 'rm -rf -- "$temporary_root"' EXIT
if [ -d /usr/local/share/minecraft-panel ]; then
    mkdir -p "$temporary_source"
    cp -a /usr/local/share/minecraft-panel/. "$temporary_source/"
else
    git clone "$repository" "$temporary_source"
fi
bash -n "$temporary_source/install.sh"

echo "A complete uninstall will now run before the fresh installation."
/usr/local/sbin/minecraft-panel-uninstall --yes

PANEL_DOMAIN="$panel_domain" \
PANEL_EMAIL="$panel_email" \
PANEL_ADMIN_USER="$panel_user" \
PANEL_ADMIN_PASSWORD="$panel_password" \
GITHUB_REPO="$repository" \
PANEL_SOURCE_DIR="$temporary_source" \
bash "$temporary_source/install.sh"
