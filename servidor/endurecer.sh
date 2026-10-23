#!/bin/bash
# =============================================================================
#  endurecer.sh - Politicas de seguridad del sistema operativo
#
#  Proyecto:    SGDM Bramajo - Empresa BoomeRam
#  Uso:         sudo ./endurecer.sh
#
#  1) SSH: sin root, sin contrasena (solo clave publica), 3 intentos.
#  2) fail2ban: bloquea 1 hora la IP que falla 3 veces por SSH.
#  3) Contrasenas de los usuarios del sistema: largo y complejidad (pwquality)
#     y vencimiento (login.defs). Son usuarios del servidor, no de la aplicacion.
#  4) Actualizaciones de seguridad automaticas (dnf-automatic).
# =============================================================================
set -euo pipefail
[ "$EUID" -eq 0 ] || { echo "Ejecutar con sudo."; exit 1; }

echo "[1/4] SSH"
# Antes de cortar el acceso por contrasena tiene que haber al menos una clave cargada.
if ! ls /home/*/.ssh/authorized_keys >/dev/null 2>&1; then
    echo "ERROR: ningun usuario tiene clave publica. Cargarla con ssh-copy-id antes de seguir."
    exit 1
fi
cat > /etc/ssh/sshd_config.d/99-bramajo.conf <<'FIN'
PermitRootLogin no
PasswordAuthentication no
MaxAuthTries 3
LoginGraceTime 30
X11Forwarding no
FIN
sshd -t && systemctl reload sshd

echo "[2/4] fail2ban"
dnf -y install epel-release >/dev/null
dnf -y install fail2ban >/dev/null
cat > /etc/fail2ban/jail.d/bramajo.local <<'FIN'
[sshd]
enabled  = true
backend  = systemd
maxretry = 3
findtime = 10m
bantime  = 1h
FIN
systemctl enable --now fail2ban
systemctl restart fail2ban

echo "[3/4] Politica de contrasenas del sistema operativo"
cat > /etc/security/pwquality.conf.d/bramajo.conf <<'FIN'
minlen = 10
ucredit = -1
lcredit = -1
dcredit = -1
retry = 3
FIN
sed -i 's/^PASS_MAX_DAYS.*/PASS_MAX_DAYS   90/; s/^PASS_MIN_DAYS.*/PASS_MIN_DAYS   1/; s/^PASS_WARN_AGE.*/PASS_WARN_AGE   7/' /etc/login.defs

echo "[4/4] Actualizaciones de seguridad automaticas"
dnf -y install dnf-automatic >/dev/null
sed -i 's/^upgrade_type.*/upgrade_type = security/; s/^apply_updates.*/apply_updates = yes/' /etc/dnf/automatic.conf
systemctl enable --now dnf-automatic.timer

echo "Listo. Comprobar con: sshd -T | grep -E 'permitroot|passwordauth'  y  fail2ban-client status sshd"
