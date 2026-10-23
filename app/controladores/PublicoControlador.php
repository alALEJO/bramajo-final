<?php

// Lo que cualquiera puede ver sin iniciar sesión.
class PublicoControlador extends Controlador
{
    public function portada(): void
    {
        $this->vista('publico/portada', ['titulo' => 'Gestiona torneos de forma simple']);
    }

    public function inicio(): void
    {
        $torneos = array_slice(Torneo::publicos('', '', 'EN_CURSO'), 0, 4);
        $this->vista('publico/inicio', ['titulo' => 'Inicio', 'torneos' => $torneos]);
    }

    public function soporte(): void
    {
        $this->vista('publico/soporte', ['titulo' => 'Soporte']);
    }

    // «Populares» muestra los que tienen más inscriptos; «Inscripciones abiertas», los que aceptan participantes.
    public function torneos(): void
    {
        $filtro = ($_GET['filtro'] ?? '') === 'abiertas' ? 'abiertas' : 'recientes';
        $buscar = trim($_GET['buscar'] ?? '');
        $deporte = $_GET['deporte'] ?? '';
        $torneos = Torneo::publicos($buscar, $deporte, $filtro === 'abiertas' ? 'INSCRIPCION' : '');
        if ($filtro === 'recientes') {
            usort($torneos, fn($a, $b) => $b['inscriptos'] <=> $a['inscriptos']);
        }
        $this->vista('publico/torneos', [
            'titulo' => 'Torneos',
            'torneos' => $torneos,
            'filtro' => $filtro,
            'buscar' => $buscar,
            'deporte' => $deporte,
        ]);
    }

    public function torneo(): void
    {
        $torneo = Torneo::buscar($this->id());
        if (!$torneo || $torneo['estado'] === 'BORRADOR') {
            $this->noEncontrado();
        }
        $pestana = $_GET['pestana'] ?? 'detalles';
        if (!in_array($pestana, ['detalles', 'participantes', 'resultados', 'posiciones'], true)) {
            $pestana = 'detalles';
        }
        // El público solo ve resultados oficiales: los de rondas cerradas.
        $this->vista('publico/torneo', [
            'titulo' => $torneo['nombre'],
            'torneo' => $torneo,
            'pestana' => $pestana,
            'participantes' => array_filter(Participante::delTorneo((int) $torneo['id_torneo']), fn($p) => $p['estado'] === 'INSCRIPTO'),
        ] + Torneo::detalle($torneo, true));
    }

    // Descarga el acta final firmada digitalmente.
    public function acta(): void
    {
        $torneo = Torneo::buscar($this->id());
        if (!$torneo || $torneo['estado'] !== 'FINALIZADO') {
            $this->noEncontrado();
        }
        $detalle = Torneo::detalle($torneo, true);
        $posiciones = [];
        foreach ($detalle['tabla'] as $i => $fila) {
            $posiciones[] = ['posicion' => $i + 1, 'nombre' => $detalle['nombres'][$fila['id']], 'puntos' => $fila['pts']];
        }
        $acta = Firma::firmar([
            'torneo' => $torneo['nombre'],
            'formato' => $torneo['modulo_nombre'],
            'campeon' => $detalle['nombres'][$detalle['campeon']] ?? null,
            'posiciones' => $posiciones,
            'fecha' => date('Y-m-d'),
        ]);
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="acta_torneo_' . $torneo['id_torneo'] . '.json"');
        echo json_encode($acta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function verificar(): void
    {
        $resultado = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verificarCsrf();
            $acta = json_decode($_POST['acta'] ?? '', true);
            $resultado = is_array($acta) && Firma::verificar($acta);
        }
        $this->vista('publico/verificar', ['titulo' => 'Verificar acta', 'resultado' => $resultado]);
    }

    public function noExiste(): void
    {
        $this->noEncontrado();
    }
}
