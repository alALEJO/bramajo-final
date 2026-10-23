<?php

class PerfilControlador extends Controlador
{
    public function ver(array $errores = []): void
    {
        $sesion = $this->exigirSesion();
        $usuario = Usuario::buscar($sesion['id']);

        $torneos = [];
        foreach (Participante::torneosDeUsuario($sesion['id']) as $fila) {
            $torneo = Torneo::buscar((int) $fila['id_torneo']);
            $detalle = Torneo::detalle($torneo, false);
            $posicion = array_search((int) $fila['id_participante'], array_column($detalle['tabla'], 'id'), true);
            $proximo = Enfrentamiento::proximo((int) $fila['id_participante']);
            if ($proximo) {
                $rival = $proximo['id_local'] == $fila['id_participante'] ? $proximo['id_visitante'] : $proximo['id_local'];
                $proximo['rival'] = $detalle['nombres'][$rival] ?? '';
            }
            $torneos[] = $fila + ['posicion' => $posicion === false ? null : $posicion + 1, 'proximo' => $proximo];
        }

        $this->vista('participante/perfil', [
            'titulo' => 'Perfil',
            'datos' => $usuario + ['telefono_visible' => Usuario::telefono($usuario)],
            'torneos' => $torneos,
            'errores' => $errores,
        ]);
    }

    public function actualizar(): void
    {
        $this->verificarCsrf();
        $sesion = $this->exigirSesion();
        $v = new Validador($_POST);
        $v->requerido('nombre', 'El nombre')->texto('nombre', 'El nombre', 2, 60)
          ->requerido('apellido', 'El apellido')->texto('apellido', 'El apellido', 2, 60);
        if ($v->valor('telefono') !== '') {
            $v->texto('telefono', 'El teléfono', 8, 20);
        }
        if (!$v->esValido()) {
            $this->ver($v->errores());
            return;
        }
        Usuario::actualizarDatos($sesion['id'], $v->valor('nombre'), $v->valor('apellido'), $v->valor('telefono'));
        $_SESSION['usuario']['nombre'] = $v->valor('nombre');
        $this->redirigir('/perfil', 'exito', 'Tus datos se guardaron.');
    }

    public function cambiarPassword(): void
    {
        $this->verificarCsrf();
        $sesion = $this->exigirSesion();
        $v = new Validador($_POST);
        $v->password('nueva');
        if (!Usuario::verificarPassword(Usuario::buscar($sesion['id']), $_POST['actual'] ?? '')) {
            $v->agregarError('actual', 'La contraseña actual no es correcta.');
        }
        if (!$v->esValido()) {
            $this->ver($v->errores());
            return;
        }
        Usuario::cambiarPassword($sesion['id'], $_POST['nueva']);
        $this->redirigir('/perfil', 'exito', 'Contraseña cambiada.');
    }

    // El participante pide entrar a un torneo individual; queda pendiente hasta que el organizador acepte.
    public function inscribirme(): void
    {
        $this->verificarCsrf();
        $sesion = $this->exigirRol('PARTICIPANTE');
        $torneo = Torneo::buscar($this->id());
        if (!$torneo || $torneo['modalidad'] !== 'INDIVIDUAL') {
            $this->redirigir('/torneos?filtro=abiertas', 'error', 'En los torneos por equipos inscribe el organizador.');
        }
        try {
            Participante::inscribir($torneo, $sesion['id'], null, 'PENDIENTE');
            $this->redirigir('/torneos?filtro=abiertas', 'exito', 'Solicitud enviada. El organizador debe aceptarla.');
        } catch (DomainException $error) {
            $this->redirigir('/torneos?filtro=abiertas', 'error', $error->getMessage());
        }
    }
}
