<?php

class Conexion
{
    private static ?PDO $pdo = null;

    // Una sola conexión por pedido. Los datos vienen de variables de entorno (archivo .env).
    public static function obtener(): PDO
    {
        if (self::$pdo === null) {
            $dsn = getenv('DB_DSN') ?: 'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=bramajo;charset=utf8mb4';
            
            // Si es SQLite en PHP 8.4+, podemos instanciarlo directamente como Pdo\Sqlite para usar su API nativa
            if (str_starts_with($dsn, 'sqlite:') && class_exists(\Pdo\Sqlite::class)) {
                self::$pdo = new \Pdo\Sqlite($dsn, getenv('DB_USUARIO') ?: null, getenv('DB_CLAVE') ?: null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } else {
                self::$pdo = new PDO($dsn, getenv('DB_USUARIO') ?: 'bramajo_app', getenv('DB_CLAVE') ?: '', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }

            if (self::$pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                // Solo para la prueba sin Docker: las versiones viejas de SQLite no traen CONCAT.
                if (method_exists(self::$pdo, 'createFunction')) {
                    self::$pdo->createFunction('CONCAT', fn(...$partes) => implode('', $partes));
                } elseif (method_exists(self::$pdo, 'sqliteCreateFunction')) {
                    @self::$pdo->sqliteCreateFunction('CONCAT', fn(...$partes) => implode('', $partes));
                }
            }
        }
        return self::$pdo;
    }

    public static function consultar(string $sql, array $valores = []): PDOStatement
    {
        $stmt = self::obtener()->prepare($sql);
        $stmt->execute($valores);
        return $stmt;
    }
}