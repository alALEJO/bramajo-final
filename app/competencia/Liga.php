<?php

// Todos contra todos con el método del círculo: uno queda fijo y el resto gira un lugar por fecha.
class Liga extends ModuloCompetencia
{
    public function totalRondas(int $cantidad, array $torneo): int
    {
        return $cantidad % 2 === 0 ? $cantidad - 1 : $cantidad;
    }

    protected function cruces(array $ids, array $rondas, array $torneo): array
    {
        if (count($ids) % 2 === 1) {
            $ids[] = null;
        }
        $fijo = array_shift($ids);
        for ($i = 0; $i < count($rondas); $i++) {
            array_unshift($ids, array_pop($ids));
        }
        $lista = array_merge([$fijo], $ids);

        $cruces = [];
        $total = count($lista);
        for ($i = 0; $i < $total / 2; $i++) {
            $local = $lista[$i];
            $visitante = $lista[$total - 1 - $i];
            $cruces[] = $local === null ? [$visitante, null] : [$local, $visitante];
        }
        return $cruces;
    }

    protected function nombreRonda(int $numero, int $cruces): string
    {
        return "Fecha $numero";
    }
}
