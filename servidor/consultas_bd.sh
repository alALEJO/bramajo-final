#!/bin/bash
# =============================================================================
#  consultas_bd.sh - Consultas y monitoreo de la base de datos de Bramajo
#
#  Proyecto:    SGDM Bramajo - Empresa BoomeRam
#  Uso:         sudo ./consultas_bd.sh
#
#  Usa el usuario bramajo_backup, que solo puede leer: desde este menu no se
#  puede romper nada aunque se escriba mal una opcion.
# =============================================================================
set -uo pipefail

CONTENEDOR_DB="bramajo_db"
BASE="bramajo"
USUARIO_BD="bramajo_backup"
ARCHIVO_PASS="/etc/bramajo/backup.pass"

[ -r "$ARCHIVO_PASS" ] || { echo "No se puede leer $ARCHIVO_PASS (ejecutar con sudo)."; exit 1; }
PASS=$(cat "$ARCHIVO_PASS")

sql() {
    docker exec -e MYSQL_PWD="$PASS" "$CONTENEDOR_DB" mysql --user="$USUARIO_BD" --table --execute="$1" "$BASE"
}

pausa() { read -r -p "Enter para volver al menu..." _; }

while true; do
    clear
    echo "=== Consultas de la base de datos de Bramajo ==="
    echo " 1) Estado del motor (version, tiempo encendido, conexiones)"
    echo " 2) Filas y tamano de cada tabla"
    echo " 3) Usuarios por rol y por estado"
    echo " 4) Cuentas bloqueadas ahora"
    echo " 5) Intentos de ingreso fallidos de las ultimas 24 horas"
    echo " 6) Ultimos 20 cambios de la auditoria"
    echo " 7) Torneos por estado y por formato"
    echo " 8) Conexiones abiertas en este momento"
    echo " 9) Permisos del usuario de consulta (DCL)"
    echo " 0) Salir"
    read -r -p "Opcion: " op
    case "$op" in
        1) sql "SELECT VERSION() AS version, NOW() AS hora;
                SHOW GLOBAL STATUS WHERE Variable_name IN ('Uptime','Threads_connected','Questions','Slow_queries');" ;;
        2) sql "SELECT table_name AS tabla, table_rows AS filas_aprox,
                       ROUND((data_length + index_length) / 1024, 1) AS kb
                FROM information_schema.tables WHERE table_schema = '$BASE' ORDER BY kb DESC;" ;;
        3) sql "SELECT rol, estado, COUNT(*) AS cantidad FROM usuario GROUP BY rol, estado;" ;;
        4) sql "SELECT id_usuario, email, intentos_fallidos, bloqueado_hasta FROM usuario WHERE bloqueado_hasta > NOW();" ;;
        5) sql "SELECT fecha_hora, ip, JSON_UNQUOTE(valor_nuevo->'$.email') AS correo FROM auditoria
                WHERE accion = 'LOGIN_FALLIDO' AND fecha_hora > NOW() - INTERVAL 1 DAY ORDER BY fecha_hora DESC;" ;;
        6) sql "SELECT fecha_hora, id_usuario, accion, tabla_afectada, id_registro FROM auditoria ORDER BY id_auditoria DESC LIMIT 20;" ;;
        7) sql "SELECT t.estado, m.nombre AS formato, COUNT(*) AS torneos FROM torneo t
                JOIN modulo_competencia m ON m.id_modulo = t.id_modulo GROUP BY t.estado, m.nombre;" ;;
        8) sql "SHOW PROCESSLIST;" ;;
        9) sql "SHOW GRANTS;" ;;
        0) exit 0 ;;
        *) echo "Opcion no valida." ;;
    esac
    pausa
done
