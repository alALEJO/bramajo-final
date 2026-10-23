# Imagen de la base: MySQL 8.0 con los scripts de Bramajo adentro.
# La primera vez que arranca crea las tablas, los usuarios y carga los datos de prueba.
FROM mysql:8.0
COPY 01_estructura.sql 02_permisos.sh 03_datos_prueba.sql /docker-entrypoint-initdb.d/
