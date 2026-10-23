#!/bin/bash
# =============================================================================
#  monitoreo.sh - Chequeo de salud del servidor y de Bramajo
#
#  Proyecto:    SGDM Bramajo - Empresa BoomeRam
#  Uso:         ./monitoreo.sh          (informe en pantalla)
#               ./monitoreo.sh cron     (solo registra; lo corre cron cada 5 min)
#               ./monitoreo.sh <clave>  (devuelve un valor para Zabbix, ver abajo)
#
#  Umbrales: disco 80 % aviso / 90 % critico, memoria 85 % / 95 %,
#  ultimo respaldo de mas de 26 horas, contenedores detenidos y la pagina
#  que no responde por HTTPS. Cada problema queda en el log y en el syslog,
#  donde lo toma el agente Zabbix.
# =============================================================================
set -uo pipefail

LOG="/var/log/bramajo-monitoreo.log"
DIR_RESPALDOS="/var/backups/bramajo"
PROBLEMAS=0

registrar() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG"; }
alerta()    { PROBLEMAS=$((PROBLEMAS + 1)); registrar "ALERTA: $1"; logger -p daemon.warning "Bramajo: $1"; echo "  [!] $1"; }
bien()      { echo "  [ok] $1"; }

disco()      { df --output=pcent / | tail -1 | tr -dc '0-9'; }
memoria()    { free | awk '/Mem:/ {printf "%d", ($2 - $7) * 100 / $2}'; }
horas_respaldo() {
    local ultimo
    ultimo=$(ls -t "$DIR_RESPALDOS"/bramajo_*.sql.gz 2>/dev/null | head -1)
    [ -n "$ultimo" ] && echo $(( ($(date +%s) - $(stat -c %Y "$ultimo")) / 3600 )) || echo 999
}
contenedor() {
    local estado
    estado=$(docker inspect -f '{{.State.Status}}' "$1" 2>/dev/null | tr -d '\n')
    echo "${estado:-no_existe}"
}
web()        { curl -ks -o /dev/null -w '%{http_code}' --max-time 5 https://localhost/; }

# Claves para Zabbix (UserParameter=bramajo.disco,/opt/bramajo/scripts/monitoreo.sh disco ...)
case "${1:-}" in
    disco|memoria|horas_respaldo|web) "$1"; exit 0 ;;
    db)  contenedor bramajo_db; exit 0 ;;
    app) contenedor bramajo_web; exit 0 ;;
esac

echo "=== Monitoreo de Bramajo - $(date '+%d/%m/%Y %H:%M') ==="

d=$(disco)
if   [ "$d" -ge 90 ]; then alerta "disco en ${d}% (critico)"
elif [ "$d" -ge 80 ]; then alerta "disco en ${d}%"
else bien "disco ${d}%"; fi

m=$(memoria)
if   [ "$m" -ge 95 ]; then alerta "memoria en ${m}% (critico)"
elif [ "$m" -ge 85 ]; then alerta "memoria en ${m}%"
else bien "memoria ${m}%"; fi

for c in bramajo_db bramajo_web; do
    estado=$(contenedor "$c")
    [ "$estado" = "running" ] && bien "$c corriendo" || alerta "$c en estado $estado"
done

for s in sshd docker firewalld crond fail2ban; do
    systemctl is-active --quiet "$s" 2>/dev/null && bien "servicio $s activo" || alerta "servicio $s detenido"
done

codigo=$(web)
[ "$codigo" = "200" ] && bien "la pagina responde por HTTPS" || alerta "la pagina respondio $codigo"

h=$(horas_respaldo)
[ "$h" -le 26 ] && bien "ultimo respaldo hace ${h} h" || alerta "el ultimo respaldo tiene ${h} h"

[ "$PROBLEMAS" -eq 0 ] && registrar "Todo en orden" || registrar "Problemas encontrados: $PROBLEMAS"
[ "${1:-}" = "cron" ] || echo "Problemas: $PROBLEMAS (detalle en $LOG)"
exit 0
