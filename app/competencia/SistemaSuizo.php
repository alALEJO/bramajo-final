<?php

// En cada ronda se enfrentan participantes con puntaje parecido y nunca dos veces los mismos.
class SistemaSuizo extends ModuloCompetencia
{
    protected bool $libreSumaVictoria = true;

    public function totalRondas(int $cantidad, array $torneo): int
    {
        $rondas = $torneo['cantidad_rondas'] ?: (int) ceil(log($cantidad, 2));
        return min($rondas, $cantidad - 1);
    }

    protected function cruces(array $ids, array $rondas, array $torneo): array
    {
        $orden = array_column($this->tabla($ids, $rondas, $torneo), 'id');

        $jugados = [];
        $tuvieronLibre = [];
        foreach ($rondas as $ronda) {
            foreach ($ronda['cruces'] as $c) {
                if ($c['id_visitante'] === null) {
                    $tuvieronLibre[] = (int) $c['id_local'];
                } else {
                    $jugados[$c['id_local'] . '-' . $c['id_visitante']] = true;
                    $jugados[$c['id_visitante'] . '-' . $c['id_local']] = true;
                }
            }
        }

        $libre = null;
        if (count($orden) % 2 === 1) {
            // El libre le toca al de menos puntos que todavía no lo tuvo.
            foreach (array_reverse($orden) as $id) {
                if (!in_array($id, $tuvieronLibre, true)) {
                    $libre = $id;
                    break;
                }
            }
            $libre ??= end($orden);
            $orden = array_values(array_diff($orden, [$libre]));
        }

        $cruces = [];
        while ($orden) {
            $local = array_shift($orden);
            $posicion = 0;
            foreach ($orden as $i => $rival) {
                if (!isset($jugados["$local-$rival"])) {
                    $posicion = $i;
                    break;
                }
            }
            $cruces[] = [$local, array_splice($orden, $posicion, 1)[0]];
        }
        if ($libre !== null) {
            $cruces[] = [$libre, null];
        }
        return $cruces;
    }
}
