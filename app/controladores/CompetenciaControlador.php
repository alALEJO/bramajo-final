<?php

// Rondas y resultados de un torneo.
class CompetenciaControlador extends Controlador
{
    private function torneoGestionable(int $id): array
    {
        $usuario = $this->exigirRol('ORGANIZADOR', 'ADMIN_GENERAL');
        $torneo = Torneo::buscar($id);
        if (!$torneo) {
            $this->noEncontrado();
        }
        if (!Torneo::puedeGestionar($torneo, $usuario)) {
            $this->prohibido();
        }
        return $torneo;
    }

    public function generarRonda(): void
    {
        $this->verificarCsrf();
        $torneo = $this->torneoGestionable($this->id());
        $ruta = "/gestion/torneo?id={$torneo['id_torneo']}&pestana=rondas";
        if (!in_array($torneo['estado'], ['INSCRIPCION', 'EN_CURSO'], true)) {
            $this->redirigir($ruta, 'error', 'Las rondas se generan con las inscripciones abiertas o con el torneo en curso.');
        }

        $ids = Participante::inscriptos((int) $torneo['id_torneo']);
        $rondas = Ronda::delTorneo((int) $torneo['id_torneo']);
        try {
            $nueva = ModuloCompetencia::crear($torneo['modulo'])->generarRonda($ids, $rondas, $torneo);
        } catch (DomainException $error) {
            $this->redirigir($ruta, 'error', $error->getMessage());
        }

        $numero = count($rondas) + 1;
        $fecha = date('Y-m-d 18:00:00', strtotime($torneo['fecha_inicio'] . ' +' . (7 * ($numero - 1)) . ' days'));
        Ronda::crear((int) $torneo['id_torneo'], $numero, $nueva['nombre'], $nueva['cruces'], $fecha);
        if ($torneo['estado'] === 'INSCRIPCION') {
            Torneo::cambiarEstado((int) $torneo['id_torneo'], 'EN_CURSO');
        }
        $this->redirigir($ruta, 'exito', "Se generó «{$nueva['nombre']}».");
    }

    public function cerrarRonda(): void
    {
        $this->verificarCsrf();
        $ronda = Ronda::buscar($this->id());
        $torneo = $this->torneoGestionable((int) ($ronda['id_torneo'] ?? 0));
        $ruta = "/gestion/torneo?id={$torneo['id_torneo']}&pestana=rondas";
        try {
            Ronda::cerrar((int) $ronda['id_ronda']);
        } catch (DomainException $error) {
            $this->redirigir($ruta, 'error', $error->getMessage());
        }

        $ids = Participante::inscriptos((int) $torneo['id_torneo']);
        if (ModuloCompetencia::crear($torneo['modulo'])->terminado($ids, Ronda::delTorneo((int) $torneo['id_torneo']), $torneo)) {
            Torneo::cambiarEstado((int) $torneo['id_torneo'], 'FINALIZADO');
            $this->redirigir($ruta, 'exito', 'Ronda cerrada. Era la última: el torneo quedó finalizado.');
        }
        $this->redirigir($ruta, 'exito', 'Ronda cerrada: sus resultados ya son oficiales y públicos.');
    }

    public function formularioResultado(array $errores = []): void
    {
        [$cruce, $torneo] = $this->cruce();
        $this->vista('organizador/resultado', [
            'titulo' => 'Resultado',
            'cruce' => array_merge($cruce, array_intersect_key($_POST, ['puntos_local' => 1, 'puntos_visitante' => 1])),
            'torneo' => $torneo,
            'nombres' => Participante::nombres((int) $torneo['id_torneo']),
            'errores' => $errores,
        ]);
    }

    public function guardarResultado(): void
    {
        $this->verificarCsrf();
        [$cruce, $torneo] = $this->cruce();
        $v = new Validador($_POST);
        $v->entero('puntos_local', 'El puntaje local', 0, 999)->entero('puntos_visitante', 'El puntaje visitante', 0, 999);

        if ($v->esValido() && $torneo['modulo'] === 'ELIMINACION') {
            $nuevo = ['puntos_local' => $v->valor('puntos_local'), 'puntos_visitante' => $v->valor('puntos_visitante')] + $cruce;
            if ($v->valor('puntos_local') === $v->valor('puntos_visitante')) {
                $v->agregarError('puntos_local', 'En eliminación directa no hay empate: cargá el resultado del desempate.');
            } elseif ($cruce['estado_ronda'] === 'CERRADA' && ModuloCompetencia::ganador($nuevo) !== ModuloCompetencia::ganador($cruce)) {
                $v->agregarError('puntos_local', 'El ganador ya avanzó en la llave: la corrección no puede cambiar quién ganó.');
            }
        }
        if (!$v->esValido()) {
            $this->formularioResultado($v->errores());
            return;
        }

        Enfrentamiento::guardarResultado($cruce, (int) $v->valor('puntos_local'), (int) $v->valor('puntos_visitante'));
        $this->redirigir("/gestion/torneo?id={$torneo['id_torneo']}&pestana=rondas#ronda-{$cruce['id_ronda']}", 'exito', 'Resultado guardado.');
    }

    // Cargar se puede con la ronda abierta. Corregir una ronda cerrada requiere autorización.
    private function cruce(): array
    {
        $cruce = Enfrentamiento::buscar($this->id());
        if (!$cruce || $cruce['id_visitante'] === null) {
            $this->noEncontrado();
        }
        $torneo = $this->torneoGestionable((int) $cruce['id_torneo']);
        if ($cruce['estado_ronda'] === 'CERRADA' && !Torneo::puedeCorregir($torneo, Sesion::usuario())) {
            $this->prohibido();
        }
        return [$cruce, $torneo];
    }
}
