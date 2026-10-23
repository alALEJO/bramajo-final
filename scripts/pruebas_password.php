<?php
// Pruebas de la contraseña con bcrypt. Se corre con: php scripts/pruebas_password.php
// No toca la base: prueba las mismas funciones que usa Usuario::registrar() y verificarPassword().

require __DIR__ . '/../app/nucleo/Validador.php';

$clave = 'Bramajo2026Test';
$hash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
$ok = 0;
$total = 0;

function prueba(string $id, string $nombre, bool $resultado): void
{
    global $ok, $total;
    $total++;
    $ok += $resultado ? 1 : 0;
    printf("%-5s %-55s %s\n", $id, $nombre, $resultado ? 'CUMPLE' : 'FALLA');
}

prueba('P-01', 'Contraseña correcta verifica', password_verify($clave, $hash));
prueba('P-02', 'Contraseña incorrecta no verifica', !password_verify('OtraClave2026', $hash));
prueba('P-03', 'Contraseña vacía no verifica', !password_verify('', $hash));
prueba('P-04', 'Misma contraseña con otra mayúscula no verifica', !password_verify('bramajo2026Test', $hash));
prueba('P-05', "Inyección ' OR '1'='1 no verifica", !password_verify("' OR '1'='1", $hash));
prueba('P-06', 'El hash no contiene la contraseña y mide 60', strpos($hash, $clave) === false && strlen($hash) === 60);
prueba('P-07', 'El hash indica bcrypt costo 12 ($2y$12$)', str_starts_with($hash, '$2y$12$'));
prueba('P-08', 'Dos hashes de la misma contraseña son distintos (sal)', $hash !== password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]));
$v = (new Validador(['password' => 'corta1A']))->password('password');
prueba('P-09', 'La política rechaza una contraseña de 7 caracteres', !$v->esValido());
$v = (new Validador(['password' => 'sinmayuscula2026']))->password('password');
prueba('P-10', 'La política rechaza una contraseña sin mayúscula', !$v->esValido());

echo "\nResultado: $ok de $total pruebas cumplen.\n\nComparación con otros métodos (misma contraseña):\n";
printf("  Base64 : %s\n", base64_encode($clave));
printf("  MD5    : %s\n", md5($clave));
printf("  SHA-1  : %s\n", sha1($clave));
printf("  bcrypt : %s\n", $hash);

$inicio = hrtime(true);
password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
$bcrypt = (hrtime(true) - $inicio) / 1e6;
$inicio = hrtime(true);
for ($i = 0; $i < 1000; $i++) {
    md5($clave);
}
$md5 = (hrtime(true) - $inicio) / 1e6 / 1000;
printf("\nTiempo de bcrypt (costo 12): %.1f ms\nTiempo de MD5              : %.4f ms\nbcrypt es unas %s veces más lento.\n",
       $bcrypt, $md5, number_format($bcrypt / $md5, 0, ',', '.'));
