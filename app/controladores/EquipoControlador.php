<?php

class EquipoControlador extends Controlador
{
    public function index(array $errores = []): void
    {
        $this->exigirRol('ORGANIZADOR', 'ADMIN_GENERAL');
        $this->vista('organizador/equipos', ['titulo' => 'Equipos', 'equipos' => Equipo::todos(), 'errores' => $errores]);
    }

    public function crear(): void
    {
        $this->verificarCsrf();
        $this->exigirRol('ORGANIZADOR', 'ADMIN_GENERAL');
        $v = new Validador($_POST);
        $v->requerido('nombre', 'El nombre')->texto('nombre', 'El nombre', 2, 80);
        if ($v->esValido() && Equipo::existeNombre($v->valor('nombre'))) {
            $v->agregarError('nombre', 'Ya existe un equipo con ese nombre.');
        }
        if (!$v->esValido()) {
            $this->index($v->errores());
            return;
        }
        $id = Equipo::crear($v->valor('nombre'));
        $this->redirigir("/equipo?id=$id", 'exito', 'Equipo creado. Ahora agregá sus integrantes.');
    }

    public function ver(): void
    {
        $this->exigirRol('ORGANIZADOR', 'ADMIN_GENERAL');
        $equipo = Equipo::buscar($this->id()) ?? $this->noEncontrado();
        $this->vista('organizador/equipo', [
            'titulo' => $equipo['nombre'],
            'equipo' => $equipo,
            'integrantes' => Equipo::integrantes((int) $equipo['id_equipo']),
            'jugadores' => Usuario::conRol('PARTICIPANTE'),
        ]);
    }

    public function integrante(): void
    {
        $this->verificarCsrf();
        $this->exigirRol('ORGANIZADOR', 'ADMIN_GENERAL');
        $equipo = Equipo::buscar($this->id()) ?? $this->noEncontrado();
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $ruta = "/equipo?id={$equipo['id_equipo']}";
        try {
            if (($_POST['accion'] ?? '') === 'quitar') {
                Equipo::quitarIntegrante((int) $equipo['id_equipo'], $idUsuario);
                $this->redirigir($ruta, 'exito', 'Integrante quitado.');
            }
            if ((Usuario::buscar($idUsuario)['rol'] ?? '') !== 'PARTICIPANTE') {
                throw new DomainException('Elegí un participante de la lista.');
            }
            Equipo::agregarIntegrante((int) $equipo['id_equipo'], $idUsuario);
            $this->redirigir($ruta, 'exito', 'Integrante agregado.');
        } catch (DomainException $error) {
            $this->redirigir($ruta, 'error', $error->getMessage());
        }
    }
}
