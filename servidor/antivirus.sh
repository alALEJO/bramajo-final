#!/bin/bash
# =============================================================================
#  antivirus.sh - Antivirus ClamAV en el servidor de Bramajo
#
#  Proyecto:    SGDM Bramajo - Empresa BoomeRam
#  Uso:         sudo ./antivirus.sh instalar   (una vez)
#               sudo ./antivirus.sh escanear   (lo corre cron todas las noches)
#
#  Se escanean las carpetas donde pueden aparecer archivos que no escribimos
#  nosotros: los respaldos, los scripts y /tmp. Los archivos infectados se
#  mueven a una cuarentena en lugar de borrarse, para poder revisarlos.
# =============================================================================
set -euo pipefail
[ "$EUID" -eq 0 ] || { echo "Ejecutar con sudo."; exit 1; }

CARPETAS="/opt/bramajo /var/backups/bramajo /tmp /var/tmp"
CUARENTENA="/var/lib/bramajo/cuarentena"
LOG="/var/log/bramajo-antivirus.log"

instalar() {
    dnf -y install epel-release >/dev/null
    dnf -y install clamav clamav-update >/dev/null
    sed -i 's/^Example/#Example/' /etc/freshclam.conf
    freshclam
    mkdir -p "$CUARENTENA" && chmod 700 "$CUARENTENA"
    echo "ClamAV instalado. Firmas: $(sigtool --info /var/lib/clamav/daily.c?d 2>/dev/null | grep Version)"
}

escanear() {
    freshclam --quiet || true
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Inicio del escaneo" >> "$LOG"
    # clamscan devuelve 1 si encontro algo: no es un error del script
    set +e
    clamscan -r -i --move="$CUARENTENA" $CARPETAS >> "$LOG" 2>&1
    resultado=$?
    set -e
    case $resultado in
        0) echo "[$(date '+%Y-%m-%d %H:%M:%S')] Sin amenazas" >> "$LOG" ;;
        1) echo "[$(date '+%Y-%m-%d %H:%M:%S')] ALERTA: archivos movidos a $CUARENTENA" >> "$LOG"
           logger -p auth.warning "Bramajo antivirus: amenaza detectada, ver $LOG" ;;
        *) echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR del escaneo ($resultado)" >> "$LOG" ;;
    esac
    tail -4 "$LOG"
}

case "${1:-}" in
    instalar) instalar ;;
    escanear) escanear ;;
    *) echo "Uso: $0 [instalar | escanear]"; exit 1 ;;
esac
