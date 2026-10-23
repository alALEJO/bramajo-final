<?php

class AutenticacionControlador extends Controlador
{
    // Un hash válido que no corresponde a nadie. Ver ingresar().
    private const HASH_FALSO = '$2y$12$Sfj0ruxBbyaO7wg6l1CUAOEqlsg2WRRrLzvFLyuLV4Lx.qRwQyl9S';

    public function formularioIngreso(): void
    {
        $this->vista('organizador/login', ['titulo' => 'Iniciar sesión']);
    }

    public function ingresar(): void
    {
        $this->verificarCsrf();
        $email = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->rechazar($email, 'Completá el correo y la contraseña.');
        }

        $usuario = Usuario::buscarPorEmail($email);
        if (!$usuario) {
            // Se pierde el mismo tiempo que con un usuario real, para que no se note qué correos existen.
            password_verify($password, self::HASH_FALSO);
            $this->rechazar($email);
        }
        if (Usuario::estaBloqueado($usuario)) {
            $this->rechazar($email, 'Cuenta bloqueada por intentos fallidos. Probá de nuevo en unos minutos.', $usuario);
        }
        if ($usuario['estado'] !== 'ACTIVO' || !Usuario::verificarPassword($usuario, $password)) {
            Usuario::registrarIntentoFallido($usuario);
            $this->rechazar($email, null, $usuario);
        }

        Usuario::registrarIngreso((int) $usuario['id_usuario']);
        Sesion::entrar($usuario);
        Auditoria::registrar('LOGIN', 'usuario', (int) $usuario['id_usuario']);
        $this->redirigir($usuario['rol'] === 'PARTICIPANTE' ? '/inicio' : '/gestion', 'exito', "Hola, {$usuario['nombre']}.");
    }

    private function rechazar(string $email, ?string $mensaje = null, ?array $usuario = null): never
    {
        Auditoria::registrar('LOGIN_FALLIDO', 'usuario', $usuario ? (int) $usuario['id_usuario'] : null,
                             null, ['email' => $email], $usuario ? (int) $usuario['id_usuario'] : null);
        $this->vista('organizador/login', [
            'titulo' => 'Iniciar sesión',
            'correo' => $email,
            'error' => $mensaje ?? 'Credenciales no válidas.',
        ]);
        exit;
    }

    public function formularioRegistro(): void
    {
        $this->vista('organizador/registro', ['titulo' => 'Registrarse', 'datos' => [], 'errores' => []]);
    }

    // Cualquiera puede crearse una cuenta de participante. Los organizadores los crea el administrador.
    public function registrar(): void
    {
        $this->verificarCsrf();
        $v = new Validador($_POST);
        $v->requerido('nombre', 'El nombre')->texto('nombre', 'El nombre', 2, 60)
          ->requerido('apellido', 'El apellido')->texto('apellido', 'El apellido', 2, 60)
          ->requerido('correo', 'El correo')->email('correo')
          ->password('password');
        if ($v->valor('telefono') !== '') {
            $v->texto('telefono', 'El teléfono', 8, 20);
        }
        if (($_POST['password'] ?? '') !== ($_POST['confirmar'] ?? '')) {
            $v->agregarError('confirmar', 'Las contraseñas no coinciden.');
        }
        if ($v->esValido() && Usuario::existeEmail($v->valor('correo'))) {
            $v->agregarError('correo', 'Ese correo ya está registrado.');
        }

        if (!$v->esValido()) {
            $this->vista('organizador/registro', ['titulo' => 'Registrarse', 'datos' => $_POST, 'errores' => $v->errores()]);
            return;
        }

        Usuario::registrar($v->valor('nombre'), $v->valor('apellido'), $v->valor('correo'), $_POST['password'],
                           'PARTICIPANTE', $v->valor('telefono'));
        $this->redirigir('/login', 'exito', 'Cuenta creada. Ya podés iniciar sesión.');
    }

    public function salir(): void
    {
        $this->verificarCsrf();
        Sesion::cerrar();
        header('Location: /inicio');
    }
}
