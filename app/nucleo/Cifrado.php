<?php

// Cifrado simétrico AES-256-GCM para datos personales que hay que poder leer después (el teléfono).
// A diferencia del hash de la contraseña, esto sí se puede revertir, pero solo con la clave.
class Cifrado
{
    private static function clave(): string
    {
        $archivo = (getenv('CLAVES_DIR') ?: __DIR__ . '/../../claves') . '/cifrado.key';
        return base64_decode(trim(file_get_contents($archivo)));
    }

    public static function cifrar(?string $texto): ?string
    {
        if ($texto === null || $texto === '') {
            return null;
        }
        $iv = random_bytes(12);
        $cifrado = openssl_encrypt($texto, 'aes-256-gcm', self::clave(), OPENSSL_RAW_DATA, $iv, $etiqueta);
        return base64_encode($iv . $etiqueta . $cifrado);
    }

    public static function descifrar(?string $guardado): ?string
    {
        if (!$guardado) {
            return null;
        }
        $datos = base64_decode($guardado);
        $texto = openssl_decrypt(substr($datos, 28), 'aes-256-gcm', self::clave(), OPENSSL_RAW_DATA,
                                 substr($datos, 0, 12), substr($datos, 12, 16));
        return $texto === false ? null : $texto;
    }
}
