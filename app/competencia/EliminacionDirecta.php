<?php

// Quien pierde queda afuera. La llave necesita 2, 4, 8, 16... lugares:
// si faltan participantes, los primeros pasan libres a la segunda ronda.
class EliminacionDirecta extends ModuloCompetencia
{
    public function totalRondas(int $cantidad, array $torneo): int
    {
        return (int) ceil(log($cantidad, 2));
    }

    protected function cruces(array $ids, array $rondas, array $torneo): array
    {
        if (!$rondas) {
            $libres = 2 ** $this->totalRondas(count($ids), $torneo) - count($ids);
            $cruces = array_map(fn($id) => [$id, null], array_slice($ids, 0, $libres));
            return array_merge($cruces, array_chunk(array_slice($ids, $libres), 2));
        }

        $ganadores = array_map([self::class, 'ganador'], end($rondas)['cruces']);
        return array_chunk($ganadores, 2);
    }

    protected function nombreRonda(int $numero, int $cruces): string
    {
        return match ($cruces) {
            1 => 'Final',
            2 => 'Semifinal',
            4 => 'Cuartos de final',
            8 => 'Octavos de final',
            default => "Ronda $numero",
        };
    }

    public function campeon(array $rondas): ?int
    {
        $ultima = end($rondas);
        if (!$ultima || count($ultima['cruces']) !== 1 || $ultima['estado'] !== 'CERRADA') {
            return null;
        }
        return self::ganador($ultima['cruces'][0]);
    }
}
