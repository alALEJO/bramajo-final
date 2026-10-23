<?php

class Ronda
{
    // Rondas del torneo con sus enfrentamientos adentro (clave 'cruces').
    public static function delTorneo(int $idTorneo): array
    {
        $rondas = Conexion::consultar('SELECT * FROM ronda WHERE id_torneo = ? ORDER BY numero', [$idTorneo])->fetchAll();
        foreach ($rondas as &$ronda) {
            $ronda['cruces'] = Conexion::consultar(
                'SELECT * FROM enfrentamiento WHERE id_ronda = ? ORDER BY id_enfrentamiento', [$ronda['id_ronda']]
            )->fetchAll();
        }
        return $rondas;
    }

    public static function buscar(int $id): ?array
    {
        return Conexion::consultar('SELECT * FROM ronda WHERE id_ronda = ?', [$id])->fetch() ?: null;
    }

    // La ronda y sus enfrentamientos se guardan juntos: si algo falla, no queda nada a medias.
    public static function crear(int $idTorneo, int $numero, string $nombre, array $cruces, string $fecha): int
    {
        $pdo = Conexion::obtener();
        $pdo->beginTransaction();
        try {
            Conexion::consultar('INSERT INTO ronda (id_torneo, numero, nombre) VALUES (?, ?, ?)', [$idTorneo, $numero, $nombre]);
            $id = (int) $pdo->lastInsertId();
            foreach ($cruces as [$local, $visitante]) {
                Conexion::consultar('INSERT INTO enfrentamiento (id_ronda, id_local, id_visitante, fecha_hora) VALUES (?, ?, ?, ?)',
                                    [$id, $local, $visitante, $visitante ? $fecha : null]);
            }
            $pdo->commit();
        } catch (Throwable $error) {
            $pdo->rollBack();
            throw $error;
        }
        Auditoria::registrar('ALTA', 'ronda', $id, null, ['id_torneo' => $idTorneo, 'nombre' => $nombre, 'cruces' => count($cruces)]);
        return $id;
    }

    public static function cerrar(int $id): void
    {
        $pendientes = Conexion::consultar(
            'SELECT COUNT(*) FROM enfrentamiento WHERE id_ronda = ? AND id_visitante IS NOT NULL AND puntos_local IS NULL', [$id]
        )->fetchColumn();
        if ($pendientes > 0) {
            throw new DomainException("Faltan $pendientes resultado(s) para poder cerrar la ronda.");
        }
        Conexion::consultar("UPDATE ronda SET estado = 'CERRADA' WHERE id_ronda = ?", [$id]);
        Auditoria::registrar('MODIFICACION', 'ronda', $id, ['estado' => 'ABIERTA'], ['estado' => 'CERRADA']);
    }
}
