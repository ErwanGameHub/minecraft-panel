# Minecraft Bedrock Web Panel

A web panel for installing, starting, stopping, backing up, restoring, and monitoring a Minecraft Bedrock server.

## Fresh Install

Run this on a new Debian or Ubuntu VPS:

```bash
curl -fsSL https://raw.githubusercontent.com/ErwanGameHub/minecraft-panel/refs/heads/main/install.sh | sudo bash
```

After installation, open the panel using the VPS public IP:

```text
http://YOUR_SERVER_IP
```

The installer prints the generated panel username and password when it finishes. Run `sudo menu` on the VPS to manage panel accounts or Minecraft installation.

## Install With Domain and HTTPS

Point your domain DNS `A` record to the VPS public IP first, then run:

```bash
curl -fsSL https://raw.githubusercontent.com/ErwanGameHub/minecraft-panel/refs/heads/main/install.sh | sudo PANEL_DOMAIN=your-domain.com PANEL_EMAIL=you@example.com bash
```

After installation, open:

```text
https://your-domain.com
```

## Install With Public IP HTTPS

Use this only if you want HTTPS directly on the VPS public IP:

```bash
curl -fsSL https://raw.githubusercontent.com/ErwanGameHub/minecraft-panel/refs/heads/main/install.sh | sudo PANEL_IP_HTTPS=1 PANEL_EMAIL=you@example.com bash
```

After installation, open:

```text
https://YOUR_SERVER_IP
```

## Supported Systems

- Debian 12
- Debian 13
- Ubuntu 22.04
- Ubuntu 24.04

## Minecraft Port

Use Minecraft Bedrock with:

```text
Server Address: YOUR_SERVER_IP
Port: 19132
```

Make sure your VPS provider firewall allows both TCP and UDP port `19132`.
