<?php

class Reporte
{
    public static function resumen(): array
    {
        $contar = fn(string $sql) => Conexion::consultar($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
        return [
            'usuarios' => $contar('SELECT rol, COUNT(*) FROM usuario GROUP BY rol'),
            'torneos' => $contar('SELECT estado, COUNT(*) FROM torneo GROUP BY estado'),
            'modulos' => $contar('SELECT m.nombre, COUNT(t.id_torneo) FROM modulo_competencia m
                                  LEFT JOIN torneo t ON t.id_modulo = m.id_modulo GROUP BY m.id_modulo, m.nombre'),
            'partidos' => (int) Conexion::consultar('SELECT COUNT(*) FROM enfrentamiento WHERE puntos_local IS NOT NULL')->fetchColumn(),
            'equipos' => (int) Conexion::consultar('SELECT COUNT(*) FROM equipo')->fetchColumn(),
        ];
    }
}
