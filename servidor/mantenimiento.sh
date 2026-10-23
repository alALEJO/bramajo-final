#!/bin/bash
# =============================================================================
#  mantenimiento.sh - Limpieza periodica de la base de datos Bramajo
#
#  Proyecto:    SGDM Bramajo - Empresa BoomeRam
#  Asignatura:  Administracion de Sistemas Operativos
#
#  Que hace: dos tareas de higiene que surgen de los requisitos de seguridad.
#
#    1) Libera las cuentas cuyo bloqueo temporal ya expiro.
#       Requisito RF-USR-06: 5 intentos fallidos bloquean 15 minutos.
#       La aplicacion tambien lo verifica al iniciar sesion, pero dejar los
#       registros limpios evita confusiones al consultar la tabla.
#
#  Uso:   ./mantenimiento.sh
#  Cron:  cada hora (ver instalar.sh)
# =============================================================================

set -euo pipefail

# -----------------------------------------------------------------------------
# CONFIGURACION
# -----------------------------------------------------------------------------
CONTENEDOR_DB="bramajo_db"
BASE="bramajo"
USUARIO_BD="bramajo_app"                      # necesita permiso de UPDATE sobre usuario
ARCHIVO_PASS="/etc/bramajo/app.pass"
LOG="/var/log/bramajo-mantenimiento.log"

registrar() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG"
}

trap 'registrar "ERROR: el mantenimiento fallo en la linea $LINENO"' ERR

# -----------------------------------------------------------------------------
# FUNCION AUXILIAR
# -----------------------------------------------------------------------------
# Ejecuta una consulta SQL dentro del contenedor y devuelve el resultado.
#   --skip-column-names : no imprime el encabezado, solo el valor
#   --batch             : salida en texto plano, sin bordes de tabla
# -----------------------------------------------------------------------------
consultar() {
    docker exec -e MYSQL_PWD="$PASS" "$CONTENEDOR_DB" \
        mysql --user="$USUARIO_BD" --skip-column-names --batch \
              --execute="$1" "$BASE"
}

# -----------------------------------------------------------------------------
# VERIFICACIONES PREVIAS
# -----------------------------------------------------------------------------
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTENEDOR_DB}$"; then
    registrar "ERROR: el contenedor ${CONTENEDOR_DB} no esta corriendo."
    exit 1
fi

PASS=$(cat "$ARCHIVO_PASS")

registrar "===== Inicio del mantenimiento ====="

# -----------------------------------------------------------------------------
# TAREA 1 - LIBERAR BLOQUEOS EXPIRADOS
# -----------------------------------------------------------------------------
# Buscamos las cuentas con bloqueado_hasta en el pasado y las liberamos,
# reiniciando ademas el contador de intentos fallidos.
# -----------------------------------------------------------------------------
BLOQUEADOS=$(consultar "SELECT COUNT(*) FROM usuario WHERE bloqueado_hasta IS NOT NULL AND bloqueado_hasta < NOW();")

if [ "$BLOQUEADOS" -gt 0 ]; then
    consultar "UPDATE usuario SET bloqueado_hasta = NULL, intentos_fallidos = 0 WHERE bloqueado_hasta IS NOT NULL AND bloqueado_hasta < NOW();"
    registrar "Cuentas liberadas por vencimiento del bloqueo: ${BLOQUEADOS}"
else
    registrar "No habia cuentas con bloqueo vencido."
fi

# -----------------------------------------------------------------------------
# TAREA 2 - INFORME DE ESTADO
# -----------------------------------------------------------------------------
# Deja en el log algunos numeros utiles para el monitoreo y para demostrar
# en la defensa que el sistema esta vivo y siendo mantenido.
# -----------------------------------------------------------------------------
USUARIOS=$(consultar "SELECT COUNT(*) FROM usuario WHERE estado = 'ACTIVO';")
TORNEOS=$(consultar "SELECT COUNT(*) FROM torneo;")
BLOQUEOS_ACTIVOS=$(consultar "SELECT COUNT(*) FROM usuario WHERE bloqueado_hasta IS NOT NULL AND bloqueado_hasta >= NOW();")

registrar "Estado: ${USUARIOS} usuarios activos | ${TORNEOS} torneos | ${BLOQUEOS_ACTIVOS} cuentas bloqueadas ahora"
registrar "===== Mantenimiento finalizado ====="

exit 0
