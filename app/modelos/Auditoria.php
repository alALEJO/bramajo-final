<?php

class Auditoria
{
    // Se llama cada vez que se agrega, cambia o borra algo, y en cada intento de ingreso.
    public static function registrar(string $accion, string $tabla, ?int $idRegistro = null,
                                      ?array $antes = null, ?array $despues = null, ?int $idUsuario = null): void
    {
        $idUsuario ??= Sesion::usuario()['id'] ?? null;
        Conexion::consultar(
            'INSERT INTO auditoria (id_usuario, accion, tabla_afectada, id_registro, valor_anterior, valor_nuevo, ip, fecha_hora)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$idUsuario, $accion, $tabla, $idRegistro,
             $antes ? json_encode($antes, JSON_UNESCAPED_UNICODE) : null,
             $despues ? json_encode($despues, JSON_UNESCAPED_UNICODE) : null,
             $_SERVER['REMOTE_ADDR'] ?? null, date('Y-m-d H:i:s')]
        );
    }

    public static function buscar(string $tabla = '', string $accion = ''): array
    {
        return Conexion::consultar(
            "SELECT a.*, CONCAT(u.nombre, ' ', u.apellido) AS usuario
             FROM auditoria a LEFT JOIN usuario u ON u.id_usuario = a.id_usuario
             WHERE (? = '' OR a.tabla_afectada = ?) AND (? = '' OR a.accion = ?)
             ORDER BY a.id_auditoria DESC LIMIT 200",
            [$tabla, $tabla, $accion, $accion]
        )->fetchAll();
    }
}
