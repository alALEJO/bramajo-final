<?php

// Un participante es la inscripción de una persona o de un equipo en un torneo.
class Participante
{
    private const NOMBRE = "COALESCE(e.nombre, CONCAT(u.nombre, ' ', u.apellido))";

    public static function delTorneo(int $idTorneo): array
    {
        return Conexion::consultar(
            'SELECT p.*, ' . self::NOMBRE . ' AS nombre FROM participante p
             LEFT JOIN usuario u ON u.id_usuario = p.id_usuario
             LEFT JOIN equipo e ON e.id_equipo = p.id_equipo
             WHERE p.id_torneo = ? ORDER BY p.estado, nombre',
            [$idTorneo]
        )->fetchAll();
    }

    public static function inscriptos(int $idTorneo): array
    {
        return array_map('intval', Conexion::consultar(
            "SELECT id_participante FROM participante WHERE id_torneo = ? AND estado = 'INSCRIPTO' ORDER BY id_participante",
            [$idTorneo]
        )->fetchAll(PDO::FETCH_COLUMN));
    }

    public static function nombres(int $idTorneo): array
    {
        $nombres = [];
        foreach (self::delTorneo($idTorneo) as $fila) {
            $nombres[$fila['id_participante']] = $fila['nombre'];
        }
        return $nombres;
    }

    public static function buscar(int $id): ?array
    {
        return Conexion::consultar('SELECT * FROM participante WHERE id_participante = ?', [$id])->fetch() ?: null;
    }

    public static function inscribir(array $torneo, ?int $idUsuario, ?int $idEquipo, string $estado = 'INSCRIPTO'): int
    {
        if ($torneo['estado'] !== 'INSCRIPCION') {
            throw new DomainException('El torneo no tiene las inscripciones abiertas.');
        }
        if ($torneo['inscriptos'] >= $torneo['max_participantes']) {
            throw new DomainException('El torneo ya completó su cupo.');
        }
        $repetido = Conexion::consultar('SELECT 1 FROM participante WHERE id_torneo = ? AND (id_usuario = ? OR id_equipo = ?)',
                                        [$torneo['id_torneo'], $idUsuario, $idEquipo])->fetch();
        if ($repetido) {
            throw new DomainException('Ya está inscripto en este torneo.');
        }
        Conexion::consultar('INSERT INTO participante (id_torneo, id_usuario, id_equipo, estado, fecha_inscripcion) VALUES (?, ?, ?, ?, ?)',
                            [$torneo['id_torneo'], $idUsuario, $idEquipo, $estado, date('Y-m-d H:i:s')]);
        $id = (int) Conexion::obtener()->lastInsertId();
        Auditoria::registrar('ALTA', 'participante', $id, null, ['id_torneo' => $torneo['id_torneo'], 'estado' => $estado]);
        return $id;
    }

    public static function cambiarEstado(array $participante, array $torneo, string $estado): void
    {
        if ($estado === 'INSCRIPTO' && $torneo['inscriptos'] >= $torneo['max_participantes']) {
            throw new DomainException('El torneo ya completó su cupo.');
        }
        Conexion::consultar('UPDATE participante SET estado = ? WHERE id_participante = ?', [$estado, $participante['id_participante']]);
        Auditoria::registrar('MODIFICACION', 'participante', (int) $participante['id_participante'],
                             ['estado' => $participante['estado']], ['estado' => $estado]);
    }

    // Torneos donde compite un usuario, solo o como integrante de un equipo.
    public static function torneosDeUsuario(int $idUsuario): array
    {
        return Conexion::consultar(
            "SELECT DISTINCT p.id_participante, p.estado AS inscripcion, t.id_torneo, t.nombre, t.estado, t.fecha_inicio, m.codigo AS modulo
             FROM participante p
             JOIN torneo t ON t.id_torneo = p.id_torneo
             JOIN modulo_competencia m ON m.id_modulo = t.id_modulo
             LEFT JOIN integrante_equipo i ON i.id_equipo = p.id_equipo
             WHERE p.id_usuario = ? OR i.id_usuario = ?
             ORDER BY t.fecha_inicio DESC",
            [$idUsuario, $idUsuario]
        )->fetchAll();
    }
}
