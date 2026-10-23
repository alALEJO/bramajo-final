#!/bin/bash
# Se ejecuta cada vez que arranca el contenedor de la aplicación.
# La primera vez genera las claves y el certificado; después los reutiliza (quedan en un volumen).
set -e
CLAVES=/var/www/bramajo/claves

if [ ! -f "$CLAVES/cifrado.key" ]; then
    echo "Generando clave de cifrado AES-256..."
    openssl rand -base64 32 > "$CLAVES/cifrado.key"
fi

if [ ! -f "$CLAVES/firma_privada.pem" ]; then
    echo "Generando par de claves RSA para la firma digital..."
    openssl genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:2048 -out "$CLAVES/firma_privada.pem"
    openssl pkey -in "$CLAVES/firma_privada.pem" -pubout -out "$CLAVES/firma_publica.pem"
fi

if [ ! -f "$CLAVES/ssl.crt" ]; then
    echo "Generando certificado SSL autofirmado..."
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$CLAVES/ssl.key" -out "$CLAVES/ssl.crt" \
        -subj "/C=UY/L=Montevideo/O=BoomeRam Solutions/CN=${NOMBRE_SERVIDOR:-localhost}"
fi

chown -R www-data:www-data "$CLAVES"
chmod 600 "$CLAVES"/*.key "$CLAVES"/*.pem

exec apache2-foreground
