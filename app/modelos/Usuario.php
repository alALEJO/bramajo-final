<?php

class Usuario
{
    const ROLES = ['ADMIN_GENERAL', 'ORGANIZADOR', 'PARTICIPANTE'];
    const INTENTOS_MAXIMOS = 5;
    const MINUTOS_BLOQUEO = 15;

    // Único lugar del sistema donde se guarda una contraseña, y siempre como hash bcrypt.
    public static function registrar(string $nombre, string $apellido, string $email, string $password,
                                     string $rol = 'PARTICIPANTE', ?string $telefono = null): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        Conexion::consultar(
            'INSERT INTO usuario (nombre, apellido, email, password_hash, telefono, rol, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$nombre, $apellido, mb_strtolower($email), $hash, Cifrado::cifrar($telefono), $rol, date('Y-m-d H:i:s')]
        );
        $id = (int) Conexion::obtener()->lastInsertId();
        Auditoria::registrar('ALTA', 'usuario', $id, null, ['email' => $email, 'rol' => $rol]);
        return $id;
    }

    public static function buscar(int $id): ?array
    {
        return Conexion::consultar('SELECT * FROM usuario WHERE id_usuario = ?', [$id])->fetch() ?: null;
    }

    public static function buscarPorEmail(string $email): ?array
    {
        return Conexion::consultar('SELECT * FROM usuario WHERE email = ? LIMIT 1', [mb_strtolower($email)])->fetch() ?: null;
    }

    public static function existeEmail(string $email, int $excepto = 0): bool
    {
        return (bool) Conexion::consultar('SELECT 1 FROM usuario WHERE email = ? AND id_usuario <> ?',
                                          [mb_strtolower($email), $excepto])->fetch();
    }

    public static function todos(string $buscar = ''): array
    {
        $texto = "%$buscar%";
        return Conexion::consultar(
            "SELECT * FROM usuario WHERE nombre LIKE ? OR apellido LIKE ? OR email LIKE ? ORDER BY rol, apellido, nombre",
            [$texto, $texto, $texto]
        )->fetchAll();
    }

    public static function conRol(string $rol): array
    {
        return Conexion::consultar("SELECT * FROM usuario WHERE rol = ? AND estado = 'ACTIVO' ORDER BY apellido, nombre",
                                   [$rol])->fetchAll();
    }

    public static function estaBloqueado(array $usuario): bool
    {
        return $usuario['bloqueado_hasta'] !== null && strtotime($usuario['bloqueado_hasta']) > time();
    }

    public static function verificarPassword(array $usuario, string $password): bool
    {
        $coincide = password_verify($password, $usuario['password_hash']);
        if ($coincide && password_needs_rehash($usuario['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            self::cambiarPassword((int) $usuario['id_usuario'], $password);
        }
        return $coincide;
    }

    public static function registrarIntentoFallido(array $usuario): void
    {
        $intentos = $usuario['intentos_fallidos'] + 1;
        $bloqueo = null;
        if ($intentos >= self::INTENTOS_MAXIMOS) {
            $bloqueo = date('Y-m-d H:i:s', time() + self::MINUTOS_BLOQUEO * 60);
            $intentos = 0;
        }
        Conexion::consultar('UPDATE usuario SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id_usuario = ?',
                            [$intentos, $bloqueo, $usuario['id_usuario']]);
    }

    public static function registrarIngreso(int $id): void
    {
        Conexion::consultar('UPDATE usuario SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = ?', [$id]);
    }

    public static function actualizarDatos(int $id, string $nombre, string $apellido, ?string $telefono): void
    {
        $antes = self::buscar($id);
        Conexion::consultar('UPDATE usuario SET nombre = ?, apellido = ?, telefono = ? WHERE id_usuario = ?',
                            [$nombre, $apellido, Cifrado::cifrar($telefono), $id]);
        Auditoria::registrar('MODIFICACION', 'usuario', $id,
                             ['nombre' => $antes['nombre'], 'apellido' => $antes['apellido']],
                             ['nombre' => $nombre, 'apellido' => $apellido]);
    }

    public static function cambiarPassword(int $id, string $password): void
    {
        Conexion::consultar('UPDATE usuario SET password_hash = ? WHERE id_usuario = ?',
                            [password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $id]);
        Auditoria::registrar('MODIFICACION', 'usuario', $id, null, ['password' => 'cambiada']);
    }

    public static function cambiarEstado(int $id, string $estado): void
    {
        $antes = self::buscar($id);
        Conexion::consultar('UPDATE usuario SET estado = ?, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = ?',
                            [$estado, $id]);
        Auditoria::registrar('MODIFICACION', 'usuario', $id, ['estado' => $antes['estado']], ['estado' => $estado]);
    }

    public static function telefono(array $usuario): ?string
    {
        return Cifrado::descifrar($usuario['telefono']);
    }
}
