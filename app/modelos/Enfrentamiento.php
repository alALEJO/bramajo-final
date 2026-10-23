<?php

class Enfrentamiento
{
    public static function buscar(int $id): ?array
    {
        return Conexion::consultar(
            'SELECT e.*, r.id_torneo, r.estado AS estado_ronda, r.nombre AS ronda
             FROM enfrentamiento e JOIN ronda r ON r.id_ronda = e.id_ronda WHERE e.id_enfrentamiento = ?',
            [$id]
        )->fetch() ?: null;
    }

    public static function guardarResultado(array $cruce, int $local, int $visitante): void
    {
        Conexion::consultar('UPDATE enfrentamiento SET puntos_local = ?, puntos_visitante = ? WHERE id_enfrentamiento = ?',
                            [$local, $visitante, $cruce['id_enfrentamiento']]);
        $antes = $cruce['puntos_local'] === null ? null
               : ['puntos_local' => $cruce['puntos_local'], 'puntos_visitante' => $cruce['puntos_visitante']];
        Auditoria::registrar($antes ? 'MODIFICACION' : 'ALTA', 'enfrentamiento', (int) $cruce['id_enfrentamiento'],
                             $antes, ['puntos_local' => $local, 'puntos_visitante' => $visitante]);
    }

    // Próximo partido sin resultado de un participante.
    public static function proximo(int $idParticipante): ?array
    {
        return Conexion::consultar(
            'SELECT * FROM enfrentamiento WHERE (id_local = ? OR id_visitante = ?) AND id_visitante IS NOT NULL
             AND puntos_local IS NULL ORDER BY fecha_hora LIMIT 1',
            [$idParticipante, $idParticipante]
        )->fetch() ?: null;
    }
}
