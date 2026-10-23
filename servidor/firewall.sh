#!/bin/bash
# =============================================================================
#  firewall.sh - Reglas de firewall del servidor de Bramajo (firewalld)
#
#  Proyecto:    SGDM Bramajo - Empresa BoomeRam
#  Uso:         sudo ./firewall.sh [aplicar | ver | revertir]
#
#  Politica: todo lo que no se abre esta cerrado.
#    - 80 y 443 abiertos para cualquiera (la aplicacion).
#    - 22 (SSH) solo desde la red del instituto.
#    - 3306 nunca: MySQL solo se usa dentro de la red de Docker.
#  firewalld es la interfaz de AlmaLinux sobre nftables/iptables; con
#  'ver' se muestran tambien las reglas de bajo nivel que genera.
# =============================================================================
set -euo pipefail

RED_ADMIN="${RED_ADMIN:-192.168.1.0/24}"     # red desde donde se administra
COPIA="/root/firewall_antes_$(date +%Y%m%d_%H%M%S).xml"

[ "$EUID" -eq 0 ] || { echo "Ejecutar con sudo."; exit 1; }

aplicar() {
    systemctl enable --now firewalld
    cp /etc/firewalld/zones/public.xml "$COPIA" 2>/dev/null || true
    echo "Copia de la configuracion anterior: $COPIA"

    firewall-cmd --permanent --zone=public --add-service=http
    firewall-cmd --permanent --zone=public --add-service=https
    # SSH deja de estar abierto a todos y pasa a una regla solo para la red de administracion
    firewall-cmd --permanent --zone=public --remove-service=ssh || true
    firewall-cmd --permanent --zone=public --add-rich-rule="rule family=ipv4 source address=${RED_ADMIN} service name=ssh accept"
    # Agente Zabbix: solo lo consulta el servidor de monitoreo de la red interna
    firewall-cmd --permanent --zone=public --add-rich-rule="rule family=ipv4 source address=${RED_ADMIN} port port=10050 protocol=tcp accept"
    firewall-cmd --permanent --zone=public --set-target=default
    firewall-cmd --reload
    ver
}

ver() {
    echo "=== Zona public ==="
    firewall-cmd --zone=public --list-all
    echo
    echo "=== Puertos escuchando en el servidor ==="
    ss -tulpn | grep -E ':(22|80|443|3306|10050)\b' || true
    echo
    echo "=== Reglas de bajo nivel (nftables/iptables) ==="
    nft list ruleset 2>/dev/null | grep -E 'dport|saddr' | head -20 || iptables -S | head -20
}

revertir() {
    local ultima
    ultima=$(ls -t /root/firewall_antes_*.xml 2>/dev/null | head -1)
    [ -n "$ultima" ] || { echo "No hay copias para revertir."; exit 1; }
    cp "$ultima" /etc/firewalld/zones/public.xml
    firewall-cmd --reload
    echo "Restaurada la configuracion de $ultima"
}

case "${1:-ver}" in
    aplicar)  aplicar ;;
    ver)      ver ;;
    revertir) revertir ;;
    *) echo "Uso: $0 [aplicar | ver | revertir]"; exit 1 ;;
esac
