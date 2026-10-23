#!/bin/bash
# =============================================================================
#  servidor.sh - Menu general de gestion del servidor de Bramajo
#
#  Proyecto:    SGDM Bramajo - Empresa BoomeRam
#  Uso:         sudo ./servidor.sh
#
#  Junta en un solo lugar todo lo que hace falta para operar el servidor:
#  el sistema (contenedores, versiones), la seguridad (firewall, antivirus),
#  los datos (respaldos, consultas) y los usuarios del sistema operativo.
#  Cada opcion llama al script que corresponde; este archivo no repite logica.
# =============================================================================
set -uo pipefail

DIR="$(dirname "$(readlink -f "$0")")"
SISTEMA="/opt/bramajo/sistema"            # donde estan docker-compose.yml y .env

[ "$EUID" -eq 0 ] || { echo "Ejecutar con sudo."; exit 1; }

compose() { (cd "$SISTEMA" && docker compose "$@"); }
pausa()   { read -r -p "Enter para volver al menu..." _; }

actualizar() {
    local actual nueva
    actual=$(grep '^VERSION=' "$SISTEMA/.env" | cut -d= -f2)
    read -r -p "Version actual: $actual. Version nueva (vacio cancela): " nueva
    [ -n "$nueva" ] || return
    "$DIR/backup.sh" || { echo "El respaldo previo fallo: no se actualiza."; return; }
    sed -i "s/^VERSION=.*/VERSION=$nueva/" "$SISTEMA/.env"
    compose pull && compose up -d && compose ps
}

while true; do
    clear
    echo "=============================================="
    echo "   Bramajo - Gestion del servidor ($(hostname))"
    echo "=============================================="
    echo " Sistema"
    echo "   1) Estado de los contenedores"
    echo "   2) Ver los ultimos mensajes de la aplicacion"
    echo "   3) Reiniciar la aplicacion"
    echo "   4) Actualizar a una version nueva de Docker Hub"
    echo " Seguridad"
    echo "   5) Ver reglas del firewall"
    echo "   6) Escanear con el antivirus ahora"
    echo "   7) IPs bloqueadas por fail2ban"
    echo " Datos"
    echo "   8) Hacer un respaldo ahora"
    echo "   9) Restaurar un respaldo"
    echo "  10) Consultas de la base de datos"
    echo " Monitoreo"
    echo "  11) Chequeo de salud"
    echo "  12) Uso de CPU, memoria y disco"
    echo " Usuarios"
    echo "  13) Gestion de usuarios, grupos y permisos del sistema"
    echo "   0) Salir"
    read -r -p "Opcion: " op
    case "$op" in
        1)  compose ps; docker stats --no-stream bramajo_web bramajo_db ;;
        2)  compose logs --tail 40 app ;;
        3)  compose restart app ;;
        4)  actualizar ;;
        5)  "$DIR/firewall.sh" ver ;;
        6)  "$DIR/antivirus.sh" escanear ;;
        7)  fail2ban-client status sshd ;;
        8)  "$DIR/backup.sh" ;;
        9)  ls -t /var/backups/bramajo/bramajo_*.sql.gz 2>/dev/null | head -5
            read -r -p "Archivo a restaurar (vacio cancela): " archivo
            [ -n "$archivo" ] && "$DIR/restaurar.sh" "$archivo" ;;
        10) "$DIR/consultas_bd.sh"; continue ;;
        11) "$DIR/monitoreo.sh" ;;
        12) uptime; free -h; df -h / /var/lib/docker 2>/dev/null ;;
        13) "$DIR/gestion.sh"; continue ;;
        0)  exit 0 ;;
        *)  echo "Opcion no valida." ;;
    esac
    pausa
done
