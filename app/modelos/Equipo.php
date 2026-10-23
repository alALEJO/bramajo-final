<?php

class Equipo
{
    public static function todos(): array
    {
        return Conexion::consultar(
            'SELECT e.*, (SELECT COUNT(*) FROM integrante_equipo i WHERE i.id_equipo = e.id_equipo) AS integrantes
             FROM equipo e ORDER BY e.nombre'
        )->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        return Conexion::consultar('SELECT * FROM equipo WHERE id_equipo = ?', [$id])->fetch() ?: null;
    }

    public static function existeNombre(string $nombre): bool
    {
        return (bool) Conexion::consultar('SELECT 1 FROM equipo WHERE nombre = ?', [$nombre])->fetch();
    }

    public static function crear(string $nombre): int
    {
        Conexion::consultar('INSERT INTO equipo (nombre, fecha_alta) VALUES (?, ?)', [$nombre, date('Y-m-d H:i:s')]);
        $id = (int) Conexion::obtener()->lastInsertId();
        Auditoria::registrar('ALTA', 'equipo', $id, null, ['nombre' => $nombre]);
        return $id;
    }

    public static function integrantes(int $id): array
    {
        return Conexion::consultar(
            'SELECT u.id_usuario, u.nombre, u.apellido FROM integrante_equipo i
             JOIN usuario u ON u.id_usuario = i.id_usuario WHERE i.id_equipo = ? ORDER BY u.apellido',
            [$id]
        )->fetchAll();
    }

    public static function agregarIntegrante(int $id, int $idUsuario): void
    {
        $existe = Conexion::consultar('SELECT 1 FROM integrante_equipo WHERE id_equipo = ? AND id_usuario = ?', [$id, $idUsuario])->fetch();
        if ($existe) {
            throw new DomainException('Ese jugador ya es integrante del equipo.');
        }
        Conexion::consultar('INSERT INTO integrante_equipo (id_equipo, id_usuario) VALUES (?, ?)', [$id, $idUsuario]);
        Auditoria::registrar('ALTA', 'integrante_equipo', $id, null, ['id_usuario' => $idUsuario]);
    }

    public static function quitarIntegrante(int $id, int $idUsuario): void
    {
        Conexion::consultar('DELETE FROM integrante_equipo WHERE id_equipo = ? AND id_usuario = ?', [$id, $idUsuario]);
        Auditoria::registrar('BAJA', 'integrante_equipo', $id, ['id_usuario' => $idUsuario]);
    }
}
