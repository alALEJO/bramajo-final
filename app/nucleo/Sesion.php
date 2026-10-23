<?php

class Sesion
{
    const MINUTOS_INACTIVIDAD = 30;

    public static function iniciar(): void
    {
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']),
            'samesite' => 'Lax',
        ]);
        session_start();

        $ultimo = $_SESSION['ultimo_movimiento'] ?? time();
        if (isset($_SESSION['usuario']) && time() - $ultimo > self::MINUTOS_INACTIVIDAD * 60) {
            self::cerrar();
            session_start();
            self::mensaje('error', 'Tu sesión venció por inactividad. Ingresá de nuevo.');
        }
        $_SESSION['ultimo_movimiento'] = time();
    }

    public static function entrar(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => (int) $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['rol'],
        ];
    }

    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function cerrar(): void
    {
        $_SESSION = [];
        setcookie(session_name(), '', time() - 3600, '/');
        session_destroy();
    }

    // Mensajes que se muestran una sola vez, en la página siguiente.
    public static function mensaje(string $tipo, string $texto): void
    {
        $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    public static function tomarMensaje(): ?array
    {
        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);
        return $mensaje;
    }
}
