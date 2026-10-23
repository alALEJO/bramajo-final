#!/bin/bash
# Prueba de Bramajo sin Docker (Linux o Mac): servidor de PHP + base SQLite con los datos de prueba.
cd "$(dirname "$0")"
[ -f datos.db ] || cp bramajo.db datos.db
echo "Bramajo esta funcionando en http://localhost:8000 (Ctrl+C para apagarlo)"
DB_DSN="sqlite:$PWD/datos.db" CLAVES_DIR="$PWD/claves" php -S localhost:8000 -t ../public router.php
