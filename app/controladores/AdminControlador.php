<?php

// Funciones exclusivas del administrador general.
class AdminControlador extends Controlador
{
    public function reportes(): void
    {
        $this->exigirRol('ADMIN_GENERAL');
        $this->vista('admin/reportes', ['titulo' => 'Administración', 'resumen' => Reporte::resumen()]);
    }

    public function usuarios(array $errores = []): void
    {
        $this->exigirRol('ADMIN_GENERAL');
        $buscar = trim($_GET['buscar'] ?? '');
        $this->vista('admin/usuarios', [
            'titulo' => 'Usuarios',
            'usuarios' => Usuario::todos($buscar),
            'buscar' => $buscar,
            'datos' => $_POST,
            'errores' => $errores,
        ]);
    }

    public function crearUsuario(): void
    {
        $this->verificarCsrf();
        $this->exigirRol('ADMIN_GENERAL');
        $v = new Validador($_POST);
        $v->requerido('nombre', 'El nombre')->texto('nombre', 'El nombre', 2, 60)
          ->requerido('apellido', 'El apellido')->texto('apellido', 'El apellido', 2, 60)
          ->requerido('email', 'El correo')->email('email')
          ->password('password')
          ->enLista('rol', Usuario::ROLES, 'Elegí un rol válido.');
        if ($v->esValido() && Usuario::existeEmail($v->valor('email'))) {
            $v->agregarError('email', 'Ese correo ya está registrado.');
        }
        if (!$v->esValido()) {
            $this->usuarios($v->errores());
            return;
        }
        Usuario::registrar($v->valor('nombre'), $v->valor('apellido'), $v->valor('email'), $_POST['password'], $v->valor('rol'));
        $this->redirigir('/admin/usuarios', 'exito', 'Usuario creado.');
    }

    public function estadoUsuario(): void
    {
        $this->verificarCsrf();
        $admin = $this->exigirRol('ADMIN_GENERAL');
        $usuario = Usuario::buscar($this->id());
        if (!$usuario || $usuario['id_usuario'] == $admin['id']) {
            $this->redirigir('/admin/usuarios', 'error', 'No podés cambiar el estado de tu propia cuenta.');
        }
        // Suspender, reactivar o desbloquear: en los tres casos se reinician los intentos fallidos.
        $estado = ($_POST['accion'] ?? '') === 'suspender' ? 'SUSPENDIDO' : 'ACTIVO';
        Usuario::cambiarEstado((int) $usuario['id_usuario'], $estado);
        $this->redirigir('/admin/usuarios', 'exito', 'Usuario actualizado.');
    }

    public function modulos(): void
    {
        $this->exigirRol('ADMIN_GENERAL');
        $this->vista('admin/modulos', ['titulo' => 'Módulos de competencia', 'modulos' => Modulo::todos()]);
    }

    public function cambiarModulo(): void
    {
        $this->verificarCsrf();
        $this->exigirRol('ADMIN_GENERAL');
        Modulo::cambiarActivo($this->id());
        $this->redirigir('/admin/modulos', 'exito', 'Módulo actualizado. Los torneos ya creados no cambian.');
    }

    public function auditoria(): void
    {
        $this->exigirRol('ADMIN_GENERAL');
        $tabla = $_GET['tabla'] ?? '';
        $accion = $_GET['accion'] ?? '';
        $this->vista('admin/auditoria', [
            'titulo' => 'Auditoría',
            'registros' => Auditoria::buscar($tabla, $accion),
            'tabla' => $tabla,
            'accion' => $accion,
        ]);
    }
}
