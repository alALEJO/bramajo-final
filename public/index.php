<?php

// Punto de entrada único: todo pedido llega acá y se deriva al controlador que corresponde.
require __DIR__ . '/../app/nucleo/Validador.php';
require __DIR__ . '/../app/vistas/ayudas.php';

spl_autoload_register(function (string $clase) {
    foreach (['nucleo', 'modelos', 'competencia', 'controladores'] as $carpeta) {
        $archivo = __DIR__ . "/../app/$carpeta/$clase.php";
        if (is_file($archivo)) {
            require $archivo;
            return;
        }
    }
});

date_default_timezone_set('America/Montevideo');
Sesion::iniciar();

$rutas = [
    'GET /' => [PublicoControlador::class, 'portada'],
    'GET /inicio' => [PublicoControlador::class, 'inicio'],
    'GET /soporte' => [PublicoControlador::class, 'soporte'],
    'GET /torneos' => [PublicoControlador::class, 'torneos'],
    'GET /torneo' => [PublicoControlador::class, 'torneo'],
    'GET /torneo/acta' => [PublicoControlador::class, 'acta'],
    'GET /verificar' => [PublicoControlador::class, 'verificar'],
    'POST /verificar' => [PublicoControlador::class, 'verificar'],

    'GET /login' => [AutenticacionControlador::class, 'formularioIngreso'],
    'POST /login' => [AutenticacionControlador::class, 'ingresar'],
    'GET /registro' => [AutenticacionControlador::class, 'formularioRegistro'],
    'POST /registro' => [AutenticacionControlador::class, 'registrar'],
    'POST /salir' => [AutenticacionControlador::class, 'salir'],

    'GET /perfil' => [PerfilControlador::class, 'ver'],
    'POST /perfil' => [PerfilControlador::class, 'actualizar'],
    'POST /perfil/password' => [PerfilControlador::class, 'cambiarPassword'],
    'POST /inscribirme' => [PerfilControlador::class, 'inscribirme'],

    'GET /gestion' => [TorneoControlador::class, 'index'],
    'GET /gestion/nuevo' => [TorneoControlador::class, 'nuevo'],
    'POST /gestion/nuevo' => [TorneoControlador::class, 'crear'],
    'GET /gestion/editar' => [TorneoControlador::class, 'editar'],
    'POST /gestion/editar' => [TorneoControlador::class, 'actualizar'],
    'GET /gestion/torneo' => [TorneoControlador::class, 'panel'],
    'POST /gestion/estado' => [TorneoControlador::class, 'estado'],
    'POST /gestion/inscribir' => [TorneoControlador::class, 'inscribir'],
    'POST /gestion/inscripcion' => [TorneoControlador::class, 'estadoInscripcion'],
    'POST /gestion/organizador' => [TorneoControlador::class, 'organizador'],
    'POST /gestion/ronda' => [CompetenciaControlador::class, 'generarRonda'],
    'POST /gestion/cerrar' => [CompetenciaControlador::class, 'cerrarRonda'],
    'GET /gestion/resultado' => [CompetenciaControlador::class, 'formularioResultado'],
    'POST /gestion/resultado' => [CompetenciaControlador::class, 'guardarResultado'],

    'GET /equipos' => [EquipoControlador::class, 'index'],
    'POST /equipos' => [EquipoControlador::class, 'crear'],
    'GET /equipo' => [EquipoControlador::class, 'ver'],
    'POST /equipo' => [EquipoControlador::class, 'integrante'],

    'GET /admin' => [AdminControlador::class, 'reportes'],
    'GET /admin/usuarios' => [AdminControlador::class, 'usuarios'],
    'POST /admin/usuarios' => [AdminControlador::class, 'crearUsuario'],
    'POST /admin/estado' => [AdminControlador::class, 'estadoUsuario'],
    'GET /admin/modulos' => [AdminControlador::class, 'modulos'],
    'POST /admin/modulos' => [AdminControlador::class, 'cambiarModulo'],
    'GET /admin/auditoria' => [AdminControlador::class, 'auditoria'],
];

$ruta = $_SERVER['REQUEST_METHOD'] . ' ' . rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$ruta = $ruta === $_SERVER['REQUEST_METHOD'] . ' ' ? $_SERVER['REQUEST_METHOD'] . ' /' : $ruta;

try {
    [$clase, $metodo] = $rutas[$ruta] ?? [PublicoControlador::class, 'noExiste'];
    (new $clase())->$metodo();
} catch (PDOException $error) {
    // El detalle técnico va al log del servidor, nunca a la pantalla.
    error_log($error->getMessage());
    http_response_code(500);
    echo 'Ocurrió un error. Intentá de nuevo más tarde.';
}
