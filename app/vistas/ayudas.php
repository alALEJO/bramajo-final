<?php

// Funciones cortas para no repetir HTML en las vistas.

function opciones(array $lista, ?string $elegido): string
{
    $html = '';
    foreach ($lista as $valor => $texto) {
        $marcado = (string) $valor === (string) $elegido ? 'selected' : '';
        $html .= '<option value="' . e((string) $valor) . "\" $marcado>" . e($texto) . '</option>';
    }
    return $html;
}

function csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . Validador::tokenCsrf() . '">';
}

function fecha(?string $fecha, bool $hora = false): string
{
    return $fecha ? date($hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($fecha)) : '';
}
