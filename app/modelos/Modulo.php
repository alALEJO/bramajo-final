<?php

class Modulo
{
    public static function todos(): array
    {
        return Conexion::consultar('SELECT * FROM modulo_competencia ORDER BY id_modulo')->fetchAll();
    }

    public static function activos(): array
    {
        return Conexion::consultar('SELECT * FROM modulo_competencia WHERE activo = 1 ORDER BY id_modulo')->fetchAll();
    }

    public static function cambiarActivo(int $id): void
    {
        $modulo = Conexion::consultar('SELECT * FROM modulo_competencia WHERE id_modulo = ?', [$id])->fetch();
        if (!$modulo) {
            return;
        }
        $activo = $modulo['activo'] ? 0 : 1;
        Conexion::consultar('UPDATE modulo_competencia SET activo = ? WHERE id_modulo = ?', [$activo, $id]);
        Auditoria::registrar('MODIFICACION', 'modulo_competencia', $id, ['activo' => (int) $modulo['activo']], ['activo' => $activo]);
    }
}
