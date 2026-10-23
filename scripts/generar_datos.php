<?php

// Genera base_de_datos/03_datos_prueba.sql con más de 50 registros por tabla.
// Usa las mismas clases de competencia del sistema, así las rondas y tablas son coherentes.
// Uso: php scripts/generar_datos.php

foreach (['ModuloCompetencia', 'Liga', 'EliminacionDirecta', 'SistemaSuizo'] as $clase) {
    require __DIR__ . "/../app/competencia/$clase.php";
}
mt_srand(2026);

const PASSWORD = 'Bramajo2026';
$sql = ["-- Datos de prueba generados por scripts/generar_datos.php. Contraseña de todos: " . PASSWORD, ''];

function valor($v): string
{
    return match (true) {
        $v === null => 'NULL',
        is_int($v) => (string) $v,
        default => "'" . str_replace("'", "''", (string) $v) . "'",
    };
}

function insertar(array &$sql, string $tabla, array $filas): void
{
    if (!$filas) {
        return;
    }
    $columnas = implode(', ', array_keys($filas[0]));
    foreach (array_chunk($filas, 200) as $bloque) {
        $valores = array_map(fn($f) => '(' . implode(', ', array_map('valor', $f)) . ')', $bloque);
        $sql[] = "INSERT INTO $tabla ($columnas) VALUES\n  " . implode(",\n  ", $valores) . ";";
    }
    $sql[] = '';
}

function sinTildes(string $texto): string
{
    return strtolower(strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', ' ' => '']));
}

// ---------- Usuarios ----------
$nombres = ['Lucía', 'Mateo', 'Sofía', 'Santiago', 'Valentina', 'Joaquín', 'Martina', 'Benjamín', 'Camila', 'Thiago',
            'Agustina', 'Facundo', 'Florencia', 'Nicolás', 'Micaela', 'Bruno', 'Josefina', 'Federico', 'Catalina', 'Gonzalo'];
$apellidos = ['Rodríguez', 'González', 'Fernández', 'Pérez', 'Martínez', 'López', 'Silva', 'Sosa', 'Pereira', 'Díaz',
              'Suárez', 'Méndez', 'Castro', 'Núñez', 'Cabrera', 'Ramos', 'Acosta', 'Olivera', 'Techera', 'Viera'];

$usuarios = [];
$agregarUsuario = function (string $nombre, string $apellido, string $email, string $rol) use (&$usuarios) {
    $usuarios[] = [
        'id_usuario' => count($usuarios) + 1, 'nombre' => $nombre, 'apellido' => $apellido, 'email' => $email,
        'password_hash' => password_hash(PASSWORD, PASSWORD_BCRYPT, ['cost' => 12]), 'rol' => $rol,
        'fecha_registro' => date('Y-m-d H:i:s', strtotime('2026-03-01') + count($usuarios) * 86400),
    ];
};
$agregarUsuario('Admin', 'Prueba', 'admin.prueba@bramajo.test', 'ADMIN_GENERAL');
$agregarUsuario('Orga', 'Prueba', 'orga.prueba@bramajo.test', 'ORGANIZADOR');
$agregarUsuario('Part', 'Prueba', 'part.prueba@bramajo.test', 'PARTICIPANTE');
for ($i = 0; $i < 5; $i++) {
    $agregarUsuario($nombres[$i * 3], $apellidos[$i * 4], 'organizador' . ($i + 1) . '@bramajo.test', 'ORGANIZADOR');
}
for ($i = 0; $i < 60; $i++) {
    $nombre = $nombres[$i % 20];
    $apellido = $apellidos[($i * 7 + intdiv($i, 20)) % 20];
    $agregarUsuario($nombre, $apellido, sinTildes("$nombre.$apellido") . ($i + 1) . '@correo.test', 'PARTICIPANTE');
}
$participantes = array_column(array_filter($usuarios, fn($u) => $u['rol'] === 'PARTICIPANTE'), 'id_usuario');
$organizadores = array_column(array_filter($usuarios, fn($u) => $u['rol'] === 'ORGANIZADOR'), 'id_usuario');
insertar($sql, 'usuario', $usuarios);

// ---------- Equipos ----------
$animales = ['Leones', 'Tigres', 'Halcones', 'Pumas', 'Cóndores', 'Toros', 'Lobos', 'Águilas', 'Zorros', 'Delfines'];
$barrios = ['Pocitos', 'Malvín', 'Cerro', 'Buceo', 'Prado'];
$equipos = [];
$integrantes = [];
foreach ($barrios as $barrio) {
    foreach ($animales as $animal) {
        $id = count($equipos) + 1;
        $equipos[] = ['id_equipo' => $id, 'nombre' => "$animal de $barrio", 'fecha_alta' => '2026-04-01 10:00:00'];
        $jugadores = $participantes;
        shuffle($jugadores);
        foreach (array_slice($jugadores, 0, 4) as $jugador) {
            $integrantes[] = ['id_equipo' => $id, 'id_usuario' => $jugador];
        }
    }
}
insertar($sql, 'equipo', $equipos);
insertar($sql, 'integrante_equipo', $integrantes);

// ---------- Torneos, inscripciones, rondas y resultados ----------
$disciplinas = [
    ['Fútbol', 'EQUIPO', 5], ['Básquetbol', 'EQUIPO', 25], ['Vóleibol', 'EQUIPO', 3], ['Handball', 'EQUIPO', 30],
    ['Tenis', 'INDIVIDUAL', 3], ['Pádel', 'INDIVIDUAL', 3], ['Boxeo', 'INDIVIDUAL', 3], ['Ajedrez', 'INDIVIDUAL', 1],
];
$modulos = [1 => 'LIGA', 2 => 'ELIMINACION', 3 => 'SUIZO'];
$nombresTorneo = ['Copa Primavera', 'Torneo Apertura', 'Liga Barrial', 'Copa Interliceal', 'Torneo Clausura', 'Desafío UTU'];
$sedes = ['Club Urunday', 'Gimnasio del ITS', 'Plaza de Deportes N.º 5', 'Club Malvín', 'Sala de informática'];
$estados = array_merge(array_fill(0, 8, 'BORRADOR'), array_fill(0, 10, 'INSCRIPCION'),
                       array_fill(0, 14, 'EN_CURSO'), array_fill(0, 18, 'FINALIZADO'));

$torneos = $organizaTorneo = $inscripciones = $rondasSql = $cruces = $auditoria = [];
foreach ($estados as $i => $estado) {
    $idTorneo = $i + 1;
    [$disciplina, $modalidad, $maxPuntos] = $disciplinas[$i % count($disciplinas)];
    $idModulo = $i % 3 + 1;
    $cupo = [6, 8, 10, 12, 16][$i % 5];
    $inicio = match ($estado) {
        'FINALIZADO' => date('Y-m-d', strtotime('2026-01-10') + ($i - 32) * 7 * 86400),
        'EN_CURSO' => '2026-10-10',
        default => date('Y-m-d', strtotime('2026-11-07') + ($i - 8) * 3 * 86400),
    };
    $creador = $organizadores[$i % count($organizadores)];
    $torneo = [
        'id_torneo' => $idTorneo, 'id_creador' => $creador, 'id_modulo' => $idModulo,
        'nombre' => $nombresTorneo[$i % 6] . " de $disciplina" . ['', ' II', ' III'][intdiv($i, 24)],
        'disciplina' => $disciplina, 'descripcion' => "Torneo de $disciplina organizado con Bramajo.",
        'reglas' => 'Victoria 3 puntos, empate 1, derrota 0.', 'ubicacion' => 'Montevideo, Uruguay',
        'sede' => $sedes[$i % 5], 'modalidad' => $modalidad, 'max_participantes' => $cupo,
        'cantidad_rondas' => $idModulo === 3 ? 4 : null, 'puntos_victoria' => 3, 'puntos_empate' => 1, 'puntos_derrota' => 0,
        'fecha_inicio' => $inicio, 'fecha_fin' => null, 'estado' => $estado,
    ];
    $torneos[] = $torneo;
    $organizaTorneo[] = ['id_torneo' => $idTorneo, 'id_usuario' => $creador, 'puede_corregir' => 0];
    if ($i % 2 === 0) {
        $organizaTorneo[] = ['id_torneo' => $idTorneo, 'id_usuario' => $organizadores[($i + 1) % count($organizadores)], 'puede_corregir' => 1];
    }
    $auditoria[] = ['id_usuario' => $creador, 'accion' => 'ALTA', 'tabla_afectada' => 'torneo', 'id_registro' => $idTorneo,
                    'valor_nuevo' => json_encode(['nombre' => $torneo['nombre']], JSON_UNESCAPED_UNICODE),
                    'ip' => '172.20.0.1', 'fecha_hora' => "$inicio 09:00:00"];
    if ($estado === 'BORRADOR') {
        continue;
    }

    // Inscripciones
    $candidatos = $modalidad === 'EQUIPO' ? array_column($equipos, 'id_equipo') : $participantes;
    shuffle($candidatos);
    $cantidad = $estado === 'INSCRIPCION' ? $cupo - 2 : $cupo - ($i % 3);
    $ids = [];
    foreach (array_slice($candidatos, 0, $cantidad + ($estado === 'INSCRIPCION' && $modalidad === 'INDIVIDUAL' ? 2 : 0)) as $k => $candidato) {
        $id = count($inscripciones) + 1;
        $pendiente = $k >= $cantidad;
        $inscripciones[] = [
            'id_participante' => $id, 'id_torneo' => $idTorneo,
            'id_usuario' => $modalidad === 'INDIVIDUAL' ? $candidato : null,
            'id_equipo' => $modalidad === 'EQUIPO' ? $candidato : null,
            'estado' => $pendiente ? 'PENDIENTE' : 'INSCRIPTO', 'fecha_inscripcion' => date('Y-m-d H:i:s', strtotime($inicio) - 10 * 86400),
        ];
        if (!$pendiente) {
            $ids[] = $id;
        }
    }
    if ($estado === 'INSCRIPCION') {
        continue;
    }

    // Se juega el torneo ronda por ronda con el módulo real.
    $modulo = ModuloCompetencia::crear($modulos[$idModulo]);
    $total = $modulo->totalRondas(count($ids), $torneo);
    $aJugar = $estado === 'FINALIZADO' ? $total : max(1, intdiv($total, 2));
    if ($estado === 'EN_CURSO') {
        // Empezó hace unas semanas: la última ronda es la de esta semana.
        $inicio = date('Y-m-d', strtotime('2026-10-15') - ($aJugar - 1) * 7 * 86400);
        $torneos[count($torneos) - 1]['fecha_inicio'] = $inicio;
    }
    $rondas = [];
    for ($n = 1; $n <= $aJugar; $n++) {
        $nueva = $modulo->generarRonda($ids, $rondas, $torneo);
        $idRonda = count($rondasSql) + 1;
        $ultimaAbierta = $estado === 'EN_CURSO' && $n === $aJugar;
        $ronda = ['estado' => $ultimaAbierta ? 'ABIERTA' : 'CERRADA', 'cruces' => []];
        $fecha = date('Y-m-d 18:00:00', strtotime($inicio) + ($n - 1) * 7 * 86400);
        foreach ($nueva['cruces'] as $k => [$local, $visitante]) {
            $jugado = $visitante !== null && (!$ultimaAbierta || $k % 2 === 0);
            $pl = $jugado ? mt_rand(0, $maxPuntos) : null;
            $pv = $jugado ? mt_rand(0, $maxPuntos) : null;
            if ($jugado && $idModulo === 2 && $pl === $pv) {
                $pl++;
            }
            $cruce = ['id_enfrentamiento' => count($cruces) + 1, 'id_ronda' => $idRonda, 'id_local' => $local, 'id_visitante' => $visitante,
                      'puntos_local' => $pl, 'puntos_visitante' => $pv, 'fecha_hora' => $visitante ? $fecha : null];
            $cruces[] = $cruce;
            $ronda['cruces'][] = $cruce;
        }
        $rondas[] = $ronda;
        $rondasSql[] = ['id_ronda' => $idRonda, 'id_torneo' => $idTorneo, 'numero' => $n, 'nombre' => $nueva['nombre'], 'estado' => $ronda['estado']];
    }
    if ($estado === 'FINALIZADO') {
        $torneos[count($torneos) - 1]['fecha_fin'] = substr($fecha, 0, 10);
    }
}

insertar($sql, 'torneo', $torneos);
insertar($sql, 'organizador_torneo', $organizaTorneo);
insertar($sql, 'participante', $inscripciones);
insertar($sql, 'ronda', $rondasSql);
insertar($sql, 'enfrentamiento', $cruces);

foreach ($usuarios as $u) {
    $auditoria[] = ['id_usuario' => $u['id_usuario'], 'accion' => 'ALTA', 'tabla_afectada' => 'usuario', 'id_registro' => $u['id_usuario'],
                    'valor_nuevo' => json_encode(['email' => $u['email'], 'rol' => $u['rol']]), 'ip' => '172.20.0.1',
                    'fecha_hora' => $u['fecha_registro']];
}
usort($auditoria, fn($a, $b) => strcmp($a['fecha_hora'], $b['fecha_hora']));
insertar($sql, 'auditoria', $auditoria);

file_put_contents(__DIR__ . '/../base_de_datos/03_datos_prueba.sql', implode("\n", $sql));
printf("usuarios %d, equipos %d, integrantes %d, torneos %d, organizadores %d, participantes %d, rondas %d, enfrentamientos %d, auditoria %d\n",
       count($usuarios), count($equipos), count($integrantes), count($torneos), count($organizaTorneo),
       count($inscripciones), count($rondasSql), count($cruces), count($auditoria));
