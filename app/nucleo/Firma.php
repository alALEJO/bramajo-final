<?php

// Firma digital RSA del acta final de un torneo. Con la clave privada se firma
// y con la pública cualquiera puede comprobar que el acta no fue modificada.
class Firma
{
    private static function archivo(string $nombre): string
    {
        return (getenv('CLAVES_DIR') ?: __DIR__ . '/../../claves') . "/$nombre";
    }

    public static function firmar(array $datos): array
    {
        $privada = openssl_pkey_get_private(file_get_contents(self::archivo('firma_privada.pem')));
        openssl_sign(json_encode($datos, JSON_UNESCAPED_UNICODE), $firma, $privada, OPENSSL_ALGO_SHA256);
        return ['datos' => $datos, 'firma' => base64_encode($firma)];
    }

    public static function verificar(array $acta): bool
    {
        if (!isset($acta['datos'], $acta['firma']) || !is_array($acta['datos'])) {
            return false;
        }
        $publica = openssl_pkey_get_public(file_get_contents(self::archivo('firma_publica.pem')));
        $texto = json_encode($acta['datos'], JSON_UNESCAPED_UNICODE);
        return openssl_verify($texto, base64_decode($acta['firma']), $publica, OPENSSL_ALGO_SHA256) === 1;
    }
}
