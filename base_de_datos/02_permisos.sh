#!/bin/bash
# DCL: usuarios de la base con los permisos justos.
# Lo ejecuta el contenedor de MySQL la primera vez que crea la base.
set -e

mysql -uroot -p"$MYSQL_ROOT_PASSWORD" <<SQL
CREATE USER IF NOT EXISTS 'bramajo_app'@'%' IDENTIFIED BY '${DB_CLAVE}';
CREATE USER IF NOT EXISTS 'bramajo_backup'@'%' IDENTIFIED BY '${DB_CLAVE_BACKUP}';

-- La aplicación lee y modifica datos, pero no puede cambiar la estructura.
GRANT SELECT, INSERT, UPDATE, DELETE ON bramajo.usuario TO 'bramajo_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON bramajo.torneo TO 'bramajo_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON bramajo.organizador_torneo TO 'bramajo_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON bramajo.equipo TO 'bramajo_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON bramajo.integrante_equipo TO 'bramajo_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON bramajo.participante TO 'bramajo_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON bramajo.ronda TO 'bramajo_app'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON bramajo.enfrentamiento TO 'bramajo_app'@'%';
GRANT SELECT, UPDATE ON bramajo.modulo_competencia TO 'bramajo_app'@'%';

-- La auditoría solo se agrega y se lee: nadie puede borrarla ni cambiarla desde el sistema.
GRANT SELECT, INSERT ON bramajo.auditoria TO 'bramajo_app'@'%';

-- Usuario para respaldos (backup.sh) y monitoreo: solo lectura.
GRANT SELECT, LOCK TABLES, SHOW VIEW ON bramajo.* TO 'bramajo_backup'@'%';
GRANT PROCESS ON *.* TO 'bramajo_backup'@'%';

FLUSH PRIVILEGES;
SQL
