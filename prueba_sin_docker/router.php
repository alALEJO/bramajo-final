<?php
// Para el servidor de prueba de PHP: los archivos que existen (css, js, imágenes) se sirven tal cual
// y todo lo demás va a index.php, igual que hace el .htaccess en Apache.
$archivo = __DIR__ . '/../public' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (is_file($archivo)) {
    return false;
}
require __DIR__ . '/../public/index.php';
