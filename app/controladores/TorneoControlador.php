<?php

// Gestión de torneos: la usan el organizador (solo sus torneos) y el administrador (todos).
class TorneoControlador extends Controlador
{
    private function gestor(): array
    {
        return $this->exigirRol('ORGANIZADOR', 'ADMIN_GENERAL');
    }

    // Busca el torneo y comprueba que el usuario pueda gestionarlo.
    private function torneo(array $usuario): array
    {
        $torneo = Torneo::buscar($this->id());
        if (!$torneo) {
            $this->noEncontrado();
        }
        if (!Torneo::puedeGestionar($torneo, $usuario)) {
            $this->prohibido();
        }
        return $torneo;
    }

    public function index(): void
    {
        $usuario = $this->gestor();
        $this->vista('organizador/misTorneos', ['titulo' => 'Mis torneos', 'torneos' => Torneo::gestionables($usuario)]);
    }

    public function nuevo(): void
    {
        $this->gestor();
        $this->formulario(['puntos_victoria' => 3, 'puntos_empate' => 1, 'puntos_derrota' => 0]);
    }

    public function crear(): void
    {
        $this->verificarCsrf();
        $usuario = $this->gestor();
        $v = $this->validar($_POST);
        if (!$v->esValido()) {
            $this->formulario($_POST, $v->errores());
            return;
        }
        $id = Torneo::crear($this->datos($v), $usuario);
        $this->redirigir("/gestion/torneo?id=$id", 'exito', 'Torneo creado en borrador. Cuando esté listo, abrí las inscripciones.');
    }

    public function editar(): void
    {
        $torneo = $this->torneo($this->gestor());
        $this->formulario($torneo);
    }

    public function actualizar(): void
    {
        $this->verificarCsrf();
        $torneo = $this->torneo($this->gestor());
        if (!in_array($torneo['estado'], ['BORRADOR', 'INSCRIPCION'], true)) {
            $this->redirigir("/gestion/torneo?id={$torneo['id_torneo']}", 'error', 'Un torneo empezado ya no se puede configurar.');
        }
        $v = $this->validar($_POST);
        if (!$v->esValido()) {
            $this->formulario($_POST + $torneo, $v->errores());
            return;
        }
        Torneo::actualizar((int) $torneo['id_torneo'], $this->datos($v));
        $this->redirigir("/gestion/torneo?id={$torneo['id_torneo']}", 'exito', 'Torneo actualizado.');
    }

    public function panel(): void
    {
        $usuario = $this->gestor();
        $torneo = $this->torneo($usuario);
        $candidatos = $torneo['modalidad'] === 'EQUIPO' ? Equipo::todos() : Usuario::conRol('PARTICIPANTE');
        $pestana = $_GET['pestana'] ?? (in_array($torneo['estado'], ['EN_CURSO', 'FINALIZADO'], true) ? 'rondas' : 'inscripciones');
        if ($pestana === 'organizadores' && $usuario['rol'] !== 'ADMIN_GENERAL') {
            $pestana = 'inscripciones';
        }
        $this->vista('organizador/panel', [
            'titulo' => $torneo['nombre'],
            'torneo' => $torneo,
            'pestana' => $pestana,
            'participantes' => Participante::delTorneo((int) $torneo['id_torneo']),
            'candidatos' => $candidatos,
            'organizadores' => Torneo::organizadores((int) $torneo['id_torneo']),
            'posiblesOrganizadores' => Usuario::conRol('ORGANIZADOR'),
            'puedeCorregir' => Torneo::puedeCorregir($torneo, $usuario),
        ] + Torneo::detalle($torneo, false));
    }

    public function estado(): void
    {
        $this->verificarCsrf();
        $torneo = $this->torneo($this->gestor());
        $ruta = "/gestion/torneo?id={$torneo['id_torneo']}&pestana=inscripciones";
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'abrir' && $torneo['estado'] === 'BORRADOR') {
            Torneo::cambiarEstado((int) $torneo['id_torneo'], 'INSCRIPCION');
            $this->redirigir($ruta, 'exito', 'Inscripciones abiertas: el torneo ya es público.');
        }
        if ($accion === 'borrador' && $torneo['estado'] === 'INSCRIPCION') {
            Torneo::cambiarEstado((int) $torneo['id_torneo'], 'BORRADOR');
            $this->redirigir($ruta, 'exito', 'El torneo volvió a borrador y dejó de ser público.');
        }
        $this->redirigir($ruta, 'error', 'Esa acción no se puede hacer en el estado actual.');
    }

    public function inscribir(): void
    {
        $this->verificarCsrf();
        $torneo = $this->torneo($this->gestor());
        $id = (int) ($_POST['candidato'] ?? 0);
        try {
            $torneo['modalidad'] === 'EQUIPO'
                ? Participante::inscribir($torneo, null, $id)
                : Participante::inscribir($torneo, $id, null);
            $this->redirigir("/gestion/torneo?id={$torneo['id_torneo']}&pestana=inscripciones", 'exito', 'Inscripción realizada.');
        } catch (DomainException $error) {
            $this->redirigir("/gestion/torneo?id={$torneo['id_torneo']}", 'error', $error->getMessage());
        } catch (PDOException) {
            $this->redirigir("/gestion/torneo?id={$torneo['id_torneo']}", 'error', 'Elegí a quién inscribir.');
        }
    }

    public function estadoInscripcion(): void
    {
        $this->verificarCsrf();
        $participante = Participante::buscar((int) ($_POST['id_participante'] ?? 0));
        $_GET['id'] = $participante['id_torneo'] ?? 0;
        $torneo = $this->torneo($this->gestor());
        $estado = $_POST['estado'] ?? '';
        $ruta = "/gestion/torneo?id={$torneo['id_torneo']}";

        if ($torneo['estado'] !== 'INSCRIPCION' || !in_array($estado, ['INSCRIPTO', 'RECHAZADO'], true)) {
            $this->redirigir($ruta, 'error', 'Las inscripciones solo se resuelven con el torneo en inscripción.');
        }
        try {
            Participante::cambiarEstado($participante, $torneo, $estado);
            $this->redirigir($ruta, 'exito', $estado === 'INSCRIPTO' ? 'Solicitud aceptada.' : 'Solicitud rechazada.');
        } catch (DomainException $error) {
            $this->redirigir($ruta, 'error', $error->getMessage());
        }
    }

    // Solo el administrador asigna o quita organizadores.
    public function organizador(): void
    {
        $this->verificarCsrf();
        $this->exigirRol('ADMIN_GENERAL');
        $torneo = $this->torneo(Sesion::usuario());
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        if (($_POST['accion'] ?? '') === 'quitar') {
            Torneo::quitarOrganizador((int) $torneo['id_torneo'], $idUsuario);
        } elseif ((Usuario::buscar($idUsuario)['rol'] ?? '') === 'ORGANIZADOR') {
            Torneo::asignarOrganizador((int) $torneo['id_torneo'], $idUsuario, isset($_POST['puede_corregir']));
        }
        $this->redirigir("/gestion/torneo?id={$torneo['id_torneo']}&pestana=organizadores", 'exito', 'Organizadores actualizados.');
    }

    private function formulario(array $torneo, array $errores = []): void
    {
        $this->vista('organizador/crearTorneo', [
            'titulo' => isset($torneo['id_torneo']) ? 'Editar torneo' : 'Crear torneo',
            'torneo' => $torneo,
            'modulos' => Modulo::activos(),
            'errores' => $errores,
        ]);
    }

    private function validar(array $datos): Validador
    {
        $v = new Validador($datos);
        $v->requerido('nombre', 'El nombre')->texto('nombre', 'El nombre', 3, 120)
          ->requerido('descripcion', 'La descripción')->texto('descripcion', 'La descripción', 5, 500)
          ->texto('reglas', 'Las reglas', 0, 500)
          ->requerido('ubicacion', 'La ubicación')->texto('ubicacion', 'La ubicación', 2, 120)
          ->texto('sede', 'La sede', 0, 120)
          ->enLista('disciplina', Torneo::DISCIPLINAS, 'Elegí un deporte de la lista.')
          ->enLista('id_modulo', array_map('strval', array_column(Modulo::activos(), 'id_modulo')), 'Elegí un formato habilitado.')
          ->enLista('modalidad', ['INDIVIDUAL', 'EQUIPO'], 'Elegí si compiten personas o equipos.')
          ->entero('max_participantes', 'El cupo', 2, 128)
          ->entero('puntos_victoria', 'Los puntos por victoria', 1, 10)
          ->entero('puntos_empate', 'Los puntos por empate', 0, 10)
          ->entero('puntos_derrota', 'Los puntos por derrota', 0, 10)
          ->fecha('fecha_inicio', 'La fecha de inicio');
        if ($v->valor('cantidad_rondas') !== '') {
            $v->entero('cantidad_rondas', 'La cantidad de rondas', 1, 15);
        }
        if ($v->valor('fecha_fin') !== '') {
            $v->fecha('fecha_fin', 'La fecha de finalización');
            if ($v->esValido() && $v->valor('fecha_fin') < $v->valor('fecha_inicio')) {
                $v->agregarError('fecha_fin', 'La fecha de finalización no puede ser anterior a la de inicio.');
            }
        }
        if ($v->esValido() && (int) $v->valor('puntos_victoria') <= (int) $v->valor('puntos_empate')) {
            $v->agregarError('puntos_victoria', 'La victoria tiene que valer más que el empate.');
        }
        return $v;
    }

    private function datos(Validador $v): array
    {
        $datos = [];
        foreach (Torneo::CAMPOS as $campo) {
            $datos[$campo] = $v->valor($campo) === '' ? null : $v->valor($campo);
        }
        return $datos;
    }
}
