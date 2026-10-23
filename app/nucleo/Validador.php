<?php

// Todas las validaciones del servidor están acá. El HTML también valida,
// pero eso es solo comodidad: cualquiera puede saltearlo.
class Validador
{
    private array $errores = [];

    public function __construct(private array $datos)
    {
    }

    public function valor(string $campo): string
    {
        return trim((string) ($this->datos[$campo] ?? ''));
    }

    public function requerido(string $campo, string $nombre): self
    {
        if ($this->valor($campo) === '') {
            $this->errores[$campo] = "$nombre es un dato obligatorio.";
        }
        return $this;
    }

    public function texto(string $campo, string $nombre, int $minimo, int $maximo): self
    {
        $largo = mb_strlen($this->valor($campo));
        if (!isset($this->errores[$campo]) && ($largo < $minimo || $largo > $maximo)) {
            $this->errores[$campo] = "$nombre debe tener entre $minimo y $maximo caracteres.";
        }
        return $this;
    }

    public function email(string $campo): self
    {
        $valor = $this->valor($campo);
        if (!isset($this->errores[$campo]) && (!filter_var($valor, FILTER_VALIDATE_EMAIL) || mb_strlen($valor) > 150)) {
            $this->errores[$campo] = 'El correo no tiene un formato válido.';
        }
        return $this;
    }

    public function password(string $campo): self
    {
        $valor = (string) ($this->datos[$campo] ?? '');
        if (mb_strlen($valor) < 10) {
            $this->errores[$campo] = 'La contraseña debe tener al menos 10 caracteres.';
        } elseif (mb_strlen($valor) > 72) {
            // bcrypt solo usa los primeros 72 bytes.
            $this->errores[$campo] = 'La contraseña no puede superar los 72 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $valor) || !preg_match('/[a-z]/', $valor) || !preg_match('/[0-9]/', $valor)) {
            $this->errores[$campo] = 'Debe combinar mayúsculas, minúsculas y números.';
        }
        return $this;
    }

    public function entero(string $campo, string $nombre, int $minimo, int $maximo): self
    {
        $numero = filter_var($this->valor($campo), FILTER_VALIDATE_INT);
        if ($numero === false || $numero < $minimo || $numero > $maximo) {
            $this->errores[$campo] = "$nombre debe ser un número entre $minimo y $maximo.";
        }
        return $this;
    }

    public function enLista(string $campo, array $permitidos, string $mensaje): self
    {
        if (!in_array($this->valor($campo), $permitidos, true)) {
            $this->errores[$campo] = $mensaje;
        }
        return $this;
    }

    public function fecha(string $campo, string $nombre): self
    {
        $fecha = DateTime::createFromFormat('Y-m-d', $this->valor($campo));
        if (!$fecha || $fecha->format('Y-m-d') !== $this->valor($campo)) {
            $this->errores[$campo] = "$nombre no es una fecha válida.";
        }
        return $this;
    }

    public function agregarError(string $campo, string $mensaje): void
    {
        $this->errores[$campo] = $mensaje;
    }

    public function esValido(): bool
    {
        return $this->errores === [];
    }

    public function errores(): array
    {
        return $this->errores;
    }

    // Todo lo que escribió un usuario pasa por acá antes de mostrarse (evita XSS).
    public static function escapar(?string $texto): string
    {
        return htmlspecialchars($texto ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function tokenCsrf(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function csrfValido(?string $token): bool
    {
        return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
    }
}

function e(?string $texto): string
{
    return Validador::escapar($texto);
}
