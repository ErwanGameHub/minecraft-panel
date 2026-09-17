#!/bin/bash
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "Run this menu as root: sudo menu"
    exit 1
fi

ACCOUNT_TOOL="/home/minecraft/panel_accounts.php"

read_password() {
    local prompt="$1"
    local password
    read -r -s -p "$prompt" password
    echo >&2
    printf '%s' "$password"
}

pause_menu() {
    echo
    read -r -p "Press Enter to continue..." _
}

while true; do
    clear
    echo "================================"
    echo " Minecraft Panel Menu"
    echo "================================"
    echo "1) Create Account"
    echo "2) Delete Account"
    echo "3) Change Password"
    echo "4) Uninstall Minecraft Panel"
    echo "5) Reinstall Minecraft Panel"
    echo "6) Reboot"
    echo "7) Exit"
    echo
    read -r -p "Select an option [1-7]: " choice

    case "$choice" in
        1)
            read -r -p "New username: " username
            password=$(read_password "New password: ")
            confirmation=$(read_password "Confirm password: ")
            if [ "$password" != "$confirmation" ]; then
                echo "Passwords do not match."
            else
                printf '%s' "$password" | php "$ACCOUNT_TOOL" create "$username" || true
            fi
            pause_menu
            ;;
        2)
            echo "Current accounts:"
            php "$ACCOUNT_TOOL" list
            read -r -p "Username to delete: " username
            read -r -p "Type DELETE to confirm: " confirmation
            if [ "$confirmation" = "DELETE" ]; then
                php "$ACCOUNT_TOOL" delete "$username" || true
            else
                echo "Cancelled."
            fi
            pause_menu
            ;;
        3)
            echo "Current accounts:"
            php "$ACCOUNT_TOOL" list
            read -r -p "Username: " username
            password=$(read_password "New password: ")
            confirmation=$(read_password "Confirm password: ")
            if [ "$password" != "$confirmation" ]; then
                echo "Passwords do not match."
            else
                printf '%s' "$password" | php "$ACCOUNT_TOOL" password "$username" || true
            fi
            pause_menu
            ;;
        4)
            echo "WARNING: This removes the complete panel, server, worlds, backups, accounts, and web stack."
            read -r -p "Type UNINSTALL to continue: " confirmation
            if [ "$confirmation" = "UNINSTALL" ]; then
                /usr/local/sbin/minecraft-panel-uninstall --yes
                exit 0
            else
                echo "Cancelled."
            fi
            pause_menu
            ;;
        5)
            echo "WARNING: Reinstall erases the current panel, worlds, backups, and accounts before installing fresh."
            read -r -p "Type REINSTALL to continue: " confirmation
            if [ "$confirmation" = "REINSTALL" ]; then
                /usr/local/sbin/minecraft-panel-reinstall
                exit 0
            else
                echo "Cancelled."
            fi
            pause_menu
            ;;
        6)
            read -r -p "Type REBOOT to reboot this VPS: " confirmation
            if [ "$confirmation" = "REBOOT" ]; then
                systemctl reboot
                exit 0
            fi
            ;;
        7) exit 0 ;;
        *) echo "Invalid option."; sleep 1 ;;
    esac
done
