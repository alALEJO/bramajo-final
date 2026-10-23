<?php

class Torneo
{
    const ESTADOS = ['BORRADOR' => 'Borrador', 'INSCRIPCION' => 'Inscripciones abiertas',
                     'EN_CURSO' => 'En curso', 'FINALIZADO' => 'Finalizado'];
    const CAMPOS = ['nombre', 'disciplina', 'descripcion', 'reglas', 'ubicacion', 'sede', 'id_modulo', 'modalidad',
                    'max_participantes', 'cantidad_rondas', 'puntos_victoria', 'puntos_empate', 'puntos_derrota',
                    'fecha_inicio', 'fecha_fin'];
    const DISCIPLINAS = ['Fútbol', 'Básquetbol', 'Vóleibol', 'Handball', 'Tenis', 'Pádel', 'Boxeo', 'Natación', 'Atletismo', 'Ajedrez'];

    private const SELECT = 'SELECT t.*, m.codigo AS modulo, m.nombre AS modulo_nombre,
                            (SELECT COUNT(*) FROM participante p WHERE p.id_torneo = t.id_torneo AND p.estado = \'INSCRIPTO\') AS inscriptos
                            FROM torneo t JOIN modulo_competencia m ON m.id_modulo = t.id_modulo';

    public static function buscar(int $id): ?array
    {
        return Conexion::consultar(self::SELECT . ' WHERE t.id_torneo = ?', [$id])->fetch() ?: null;
    }

    public static function publicos(string $buscar = '', string $disciplina = '', string $estado = ''): array
    {
        $texto = "%$buscar%";
        return Conexion::consultar(
            self::SELECT . " WHERE t.estado <> 'BORRADOR' AND (t.nombre LIKE ? OR t.sede LIKE ?)
                             AND (? = '' OR t.disciplina = ?) AND (? = '' OR t.estado = ?)
                             ORDER BY t.fecha_inicio DESC",
            [$texto, $texto, $disciplina, $disciplina, $estado, $estado]
        )->fetchAll();
    }

    // El administrador ve todos; el organizador solo los que tiene asignados.
    public static function gestionables(array $usuario): array
    {
        if ($usuario['rol'] === 'ADMIN_GENERAL') {
            return Conexion::consultar(self::SELECT . ' ORDER BY t.id_torneo DESC')->fetchAll();
        }
        return Conexion::consultar(
            self::SELECT . ' JOIN organizador_torneo o ON o.id_torneo = t.id_torneo WHERE o.id_usuario = ? ORDER BY t.id_torneo DESC',
            [$usuario['id']]
        )->fetchAll();
    }

    public static function puedeGestionar(array $torneo, array $usuario): bool
    {
        return $usuario['rol'] === 'ADMIN_GENERAL' || (bool) Conexion::consultar(
            'SELECT 1 FROM organizador_torneo WHERE id_torneo = ? AND id_usuario = ?',
            [$torneo['id_torneo'], $usuario['id']]
        )->fetch();
    }

    public static function puedeCorregir(array $torneo, array $usuario): bool
    {
        return $usuario['rol'] === 'ADMIN_GENERAL' || (bool) Conexion::consultar(
            'SELECT 1 FROM organizador_torneo WHERE id_torneo = ? AND id_usuario = ? AND puede_corregir = 1',
            [$torneo['id_torneo'], $usuario['id']]
        )->fetch();
    }

    public static function crear(array $datos, array $usuario): int
    {
        $valores = array_map(fn($campo) => $datos[$campo], self::CAMPOS);
        Conexion::consultar(
            'INSERT INTO torneo (id_creador, ' . implode(', ', self::CAMPOS) . ') VALUES (?' . str_repeat(', ?', count(self::CAMPOS)) . ')',
            array_merge([$usuario['id']], $valores)
        );
        $id = (int) Conexion::obtener()->lastInsertId();
        Auditoria::registrar('ALTA', 'torneo', $id, null, ['nombre' => $datos['nombre']]);
        if ($usuario['rol'] === 'ORGANIZADOR') {
            self::asignarOrganizador($id, $usuario['id'], false);
        }
        return $id;
    }

    public static function actualizar(int $id, array $datos): void
    {
        $antes = self::buscar($id);
        $sets = implode(', ', array_map(fn($campo) => "$campo = ?", self::CAMPOS));
        $valores = array_map(fn($campo) => $datos[$campo], self::CAMPOS);
        Conexion::consultar("UPDATE torneo SET $sets WHERE id_torneo = ?", array_merge($valores, [$id]));
        Auditoria::registrar('MODIFICACION', 'torneo', $id, array_intersect_key($antes, $datos), $datos);
    }

    public static function cambiarEstado(int $id, string $estado): void
    {
        $antes = self::buscar($id)['estado'];
        Conexion::consultar('UPDATE torneo SET estado = ? WHERE id_torneo = ?', [$estado, $id]);
        Auditoria::registrar('MODIFICACION', 'torneo', $id, ['estado' => $antes], ['estado' => $estado]);
    }

    public static function organizadores(int $id): array
    {
        return Conexion::consultar(
            'SELECT u.id_usuario, u.nombre, u.apellido, o.puede_corregir FROM organizador_torneo o
             JOIN usuario u ON u.id_usuario = o.id_usuario WHERE o.id_torneo = ? ORDER BY u.apellido',
            [$id]
        )->fetchAll();
    }

    public static function asignarOrganizador(int $id, int $idUsuario, bool $puedeCorregir): void
    {
        Conexion::consultar('DELETE FROM organizador_torneo WHERE id_torneo = ? AND id_usuario = ?', [$id, $idUsuario]);
        Conexion::consultar('INSERT INTO organizador_torneo (id_torneo, id_usuario, puede_corregir) VALUES (?, ?, ?)',
                            [$id, $idUsuario, (int) $puedeCorregir]);
        Auditoria::registrar('ALTA', 'organizador_torneo', $id, null, ['id_usuario' => $idUsuario, 'puede_corregir' => $puedeCorregir]);
    }

    public static function quitarOrganizador(int $id, int $idUsuario): void
    {
        Conexion::consultar('DELETE FROM organizador_torneo WHERE id_torneo = ? AND id_usuario = ?', [$id, $idUsuario]);
        Auditoria::registrar('BAJA', 'organizador_torneo', $id, ['id_usuario' => $idUsuario]);
    }

    // Todo lo que se muestra de un torneo: rondas, nombres, tabla y campeón.
    // Para el público la tabla se calcula solo con rondas cerradas (resultados oficiales).
    public static function detalle(array $torneo, bool $soloOficiales): array
    {
        $ids = Participante::inscriptos((int) $torneo['id_torneo']);
        $rondas = Ronda::delTorneo((int) $torneo['id_torneo']);
        $oficiales = array_values(array_filter($rondas, fn($r) => $r['estado'] === 'CERRADA'));
        $modulo = ModuloCompetencia::crear($torneo['modulo']);
        $tabla = $modulo->tabla($ids, $soloOficiales ? $oficiales : $rondas, $torneo);
        $campeon = $modulo instanceof EliminacionDirecta ? $modulo->campeon($rondas) : null;
        if (!$modulo instanceof EliminacionDirecta && $torneo['estado'] === 'FINALIZADO') {
            $campeon = $tabla[0]['id'] ?? null;
        }
        return ['nombres' => Participante::nombres((int) $torneo['id_torneo']), 'rondas' => $rondas,
                'tabla' => $tabla, 'campeon' => $campeon];
    }

    // Imagen de la tarjeta según el deporte.
    public static function imagen(string $disciplina): string
    {
        $imagenes = ['Fútbol' => 'futbol', 'Tenis' => 'tenis', 'Pádel' => 'tenis', 'Boxeo' => 'boxeo',
                     'Natación' => 'natacion', 'Atletismo' => 'natacion'];
        return '/img/' . ($imagenes[$disciplina] ?? 'basquetbol') . '.jpg';
    }
}
