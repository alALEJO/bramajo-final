<?php

// Lo que comparten todos los controladores.
abstract class Controlador
{
    protected function vista(string $nombre, array $datos = []): void
    {
        extract($datos);
        $usuario = Sesion::usuario();
        $mensaje = Sesion::tomarMensaje();
        require __DIR__ . "/../vistas/$nombre.php";
    }

    protected function redirigir(string $ruta, ?string $tipo = null, ?string $texto = null): never
    {
        if ($tipo) {
            Sesion::mensaje($tipo, $texto);
        }
        header("Location: $ruta");
        exit;
    }

    protected function exigirSesion(): array
    {
        $usuario = Sesion::usuario();
        if (!$usuario) {
            $this->redirigir('/login', 'error', 'Ingresá para continuar.');
        }
        return $usuario;
    }

    protected function exigirRol(string ...$roles): array
    {
        $usuario = $this->exigirSesion();
        if (!in_array($usuario['rol'], $roles, true)) {
            $this->prohibido();
        }
        return $usuario;
    }

    protected function prohibido(): never
    {
        http_response_code(403);
        $this->vista('publico/error', ['titulo' => 'Sin permiso', 'texto' => 'No tenés permiso para entrar a esta página.']);
        exit;
    }

    protected function noEncontrado(): never
    {
        http_response_code(404);
        $this->vista('publico/error', ['titulo' => 'No encontrado', 'texto' => 'La página que buscás no existe.']);
        exit;
    }

    // Todo formulario que modifica datos trae el token; si no coincide, no se hace nada.
    protected function verificarCsrf(): void
    {
        if (!Validador::csrfValido($_POST['csrf'] ?? null)) {
            $this->redirigir($_SERVER['HTTP_REFERER'] ?? '/', 'error', 'La sesión expiró. Volvé a intentar.');
        }
    }

    protected function id(string $campo = 'id'): int
    {
        return (int) ($_GET[$campo] ?? $_POST[$campo] ?? 0);
    }
}
