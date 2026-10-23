<?php
require __DIR__ . '/../app/nucleo/Validador.php';
foreach (['ModuloCompetencia', 'Liga', 'EliminacionDirecta', 'SistemaSuizo'] as $clase) {
    require_once __DIR__ . "/../app/competencia/$clase.php";
}
