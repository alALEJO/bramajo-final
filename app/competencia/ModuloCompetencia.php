<?php

// Cada formato de torneo hereda de esta clase y resuelve a su manera cómo armar los cruces.
// El resto del sistema no sabe qué formato tiene el torneo: llama a los mismos métodos (polimorfismo).
abstract class ModuloCompetencia
{
    protected bool $libreSumaVictoria = false;

    public static function crear(string $codigo): self
    {
        return match ($codigo) {
            'LIGA' => new Liga(),
            'ELIMINACION' => new EliminacionDirecta(),
            'SUIZO' => new SistemaSuizo(),
        };
    }

    abstract public function totalRondas(int $cantidad, array $torneo): int;

    // Devuelve una lista de cruces [local, visitante]; visitante null significa que el local queda libre.
    abstract protected function cruces(array $ids, array $rondas, array $torneo): array;

    public function generarRonda(array $ids, array $rondas, array $torneo): array
    {
        if (count($ids) < 2) {
            throw new DomainException('Se necesitan al menos 2 inscriptos.');
        }
        if ($rondas && end($rondas)['estado'] !== 'CERRADA') {
            throw new DomainException('Primero hay que cerrar la ronda anterior.');
        }
        if (count($rondas) >= $this->totalRondas(count($ids), $torneo)) {
            throw new DomainException('Ya se generaron todas las rondas del torneo.');
        }
        $cruces = $this->cruces($ids, $rondas, $torneo);
        return ['nombre' => $this->nombreRonda(count($rondas) + 1, count($cruces)), 'cruces' => $cruces];
    }

    protected function nombreRonda(int $numero, int $cruces): string
    {
        return "Ronda $numero";
    }

    public function terminado(array $ids, array $rondas, array $torneo): bool
    {
        return count($rondas) >= $this->totalRondas(count($ids), $torneo) && end($rondas)['estado'] === 'CERRADA';
    }

    // Tabla de posiciones calculada a partir de los resultados cargados. No se guarda en la base.
    public function tabla(array $ids, array $rondas, array $torneo): array
    {
        $tabla = [];
        foreach ($ids as $id) {
            $tabla[$id] = ['id' => $id, 'pj' => 0, 'g' => 0, 'e' => 0, 'p' => 0, 'pf' => 0, 'pc' => 0, 'pts' => 0];
        }
        foreach ($rondas as $ronda) {
            foreach ($ronda['cruces'] as $c) {
                if ($c['id_visitante'] === null) {
                    if ($this->libreSumaVictoria && isset($tabla[$c['id_local']])) {
                        $tabla[$c['id_local']]['pts'] += $torneo['puntos_victoria'];
                    }
                    continue;
                }
                if ($c['puntos_local'] === null) {
                    continue;
                }
                $this->sumar($tabla, (int) $c['id_local'], (int) $c['puntos_local'], (int) $c['puntos_visitante'], $torneo);
                $this->sumar($tabla, (int) $c['id_visitante'], (int) $c['puntos_visitante'], (int) $c['puntos_local'], $torneo);
            }
        }
        usort($tabla, fn($a, $b) => [$b['pts'], $b['pf'] - $b['pc'], $b['pf']] <=> [$a['pts'], $a['pf'] - $a['pc'], $a['pf']]);
        return $tabla;
    }

    private function sumar(array &$tabla, int $id, int $favor, int $contra, array $torneo): void
    {
        if (!isset($tabla[$id])) {
            return;
        }
        $fila = &$tabla[$id];
        $fila['pj']++;
        $fila['pf'] += $favor;
        $fila['pc'] += $contra;
        if ($favor > $contra) {
            $fila['g']++;
            $fila['pts'] += $torneo['puntos_victoria'];
        } elseif ($favor === $contra) {
            $fila['e']++;
            $fila['pts'] += $torneo['puntos_empate'];
        } else {
            $fila['p']++;
            $fila['pts'] += $torneo['puntos_derrota'];
        }
    }

    public static function ganador(array $cruce): ?int
    {
        if ($cruce['id_visitante'] === null) {
            return (int) $cruce['id_local'];
        }
        if ($cruce['puntos_local'] === null || $cruce['puntos_local'] == $cruce['puntos_visitante']) {
            return null;
        }
        return (int) ($cruce['puntos_local'] > $cruce['puntos_visitante'] ? $cruce['id_local'] : $cruce['id_visitante']);
    }
}
