<?php
$estilos = ['infoTorneos'];
$esLlave = $torneo['modulo'] === 'ELIMINACION';
$enlace = fn($p) => "/torneo?id={$torneo['id_torneo']}&pestana=$p";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require __DIR__ . '/../base/cabeza.php'; ?>
</head>
<body>
    <header class="header">
        <div class="header__top">
            <img class="header__logo" src="/img/bramajo-logo.png" alt="Logo de BRAMAJO">
            <button class="nav-toggle" id="navToggle" aria-label="Abrir menu">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
        <nav class="header__nav" id="headerNav">
            <a class="nav__link" href="/torneos">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Volver a torneos
            </a>
            <a class="nav__link" href="/soporte">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                Soporte
            </a>
        </nav>
    </header>

    <main class="contenido-principal">
        <?php require __DIR__ . '/../base/mensaje.php'; ?>
        <section class="encabezado-torneo">
            <img class="encabezado-torneo__imagen" src="<?= Torneo::imagen($torneo['disciplina']) ?>" alt="<?= e($torneo['nombre']) ?>">
            <div class="encabezado-torneo__informacion">
                <p class="estado-torneo"><?= Torneo::ESTADOS[$torneo['estado']] ?></p>
                <h1><?= e($torneo['nombre']) ?></h1>
                <p class="descripcion-torneo"><?= e($torneo['descripcion']) ?></p>
                <?php if ($campeon): ?>
                    <p class="estado-torneo">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                        Campeon: <?= e($nombres[$campeon] ?? '') ?>
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <nav class="pestanas">
            <a class="pestana <?= $pestana === 'detalles' ? 'activa' : '' ?>" href="<?= $enlace('detalles') ?>">Detalles</a>
            <a class="pestana <?= $pestana === 'participantes' ? 'activa' : '' ?>" href="<?= $enlace('participantes') ?>">Participantes</a>
            <a class="pestana <?= $pestana === 'resultados' ? 'activa' : '' ?>" href="<?= $enlace('resultados') ?>">Resultados</a>
            <a class="pestana <?= $pestana === 'posiciones' ? 'activa' : '' ?>" href="<?= $enlace('posiciones') ?>"><?= $esLlave ? 'Llave' : 'Posiciones' ?></a>
        </nav>

        <section class="contenido-pestana">
            <?php if ($pestana === 'detalles'): ?>
                <h2>Detalles del torneo</h2>
                <div class="detalles-grid">
                    <article class="detalle"><h3>Deporte</h3><p><?= e($torneo['disciplina']) ?></p></article>
                    <article class="detalle"><h3>Formato</h3><p><?= e($torneo['modulo_nombre']) ?></p></article>
                    <article class="detalle"><h3>Fecha</h3><p><?= fecha($torneo['fecha_inicio']) ?><?= $torneo['fecha_fin'] ? ' al ' . fecha($torneo['fecha_fin']) : '' ?></p></article>
                    <article class="detalle"><h3>Lugar</h3><p><?= e(trim($torneo['sede'] . ', ' . $torneo['ubicacion'], ', ')) ?></p></article>
                    <article class="detalle"><h3>Inscriptos</h3><p><?= $torneo['inscriptos'] ?> de <?= $torneo['max_participantes'] ?> (<?= $torneo['modalidad'] === 'EQUIPO' ? 'equipos' : 'individual' ?>)</p></article>
                    <article class="detalle"><h3>Puntaje</h3><p>Victoria <?= $torneo['puntos_victoria'] ?>, empate <?= $torneo['puntos_empate'] ?>, derrota <?= $torneo['puntos_derrota'] ?></p></article>
                    <article class="detalle detalle-grande"><h3>Reglas</h3><p><?= e($torneo['reglas'] ?: 'Sin reglas especiales.') ?></p></article>
                    <?php if ($torneo['estado'] === 'FINALIZADO'): ?>
                        <article class="detalle detalle-grande">
                            <h3>Acta final</h3>
                            <p>
                                <a class="pestana" href="/torneo/acta?id=<?= $torneo['id_torneo'] ?>">Descargar acta firmada</a>
                                <a class="pestana" href="/verificar">Verificar un acta</a>
                            </p>
                        </article>
                    <?php endif; ?>
                </div>

            <?php elseif ($pestana === 'participantes'): ?>
                <h2>Participantes</h2>
                <?php if (!$participantes): ?>
                    <p class="mensaje-vacio">Todavia no hay participantes registrados.</p>
                <?php else: ?>
                    <div class="participantes-lista">
                        <?php foreach (array_values($participantes) as $numero => $participante): ?>
                            <article class="participante">
                                <span class="participante__numero"><?= $numero + 1 ?></span>
                                <p><?= e($participante['nombre']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php elseif ($pestana === 'resultados'): ?>
                <h2>Resultados</h2>
                <?php if (!$rondas): ?>
                    <p class="mensaje-vacio">Todavia no hay resultados disponibles.</p>
                <?php endif; ?>
                <?php foreach (array_reverse($rondas) as $ronda): ?>
                    <h3><?= e($ronda['nombre']) ?><?= $ronda['estado'] === 'ABIERTA' ? ' (por jugar)' : '' ?></h3>
                    <div class="resultados-lista">
                        <?php foreach ($ronda['cruces'] as $cruce): ?>
                            <?php require __DIR__ . '/_cruce.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

            <?php elseif ($esLlave): ?>
                <h2>Llave</h2>
                <?php if (!$rondas): ?><p class="mensaje-vacio">La llave se arma cuando empieza el torneo.</p><?php endif; ?>
                <?php foreach ($rondas as $ronda): ?>
                    <h3><?= e($ronda['nombre']) ?></h3>
                    <div class="resultados-lista">
                        <?php foreach ($ronda['cruces'] as $cruce): ?>
                            <?php require __DIR__ . '/_cruce.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <h2>Tabla de posiciones</h2>
                <div class="tabla-scroll">
                    <table class="tabla">
                        <tr><th>#</th><th>Participante</th><th>PJ</th><th>G</th><th>E</th><th>P</th><th>PF</th><th>PC</th><th>Pts</th></tr>
                        <?php foreach ($tabla as $i => $fila): ?>
                            <tr>
                                <td><?= $i + 1 ?></td><td><?= e($nombres[$fila['id']] ?? '') ?></td>
                                <td><?= $fila['pj'] ?></td><td><?= $fila['g'] ?></td><td><?= $fila['e'] ?></td><td><?= $fila['p'] ?></td>
                                <td><?= $fila['pf'] ?></td><td><?= $fila['pc'] ?></td><td><strong><?= $fila['pts'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        document.getElementById('navToggle').addEventListener('click', function () {
            document.getElementById('headerNav').classList.toggle('abierto');
        });
    </script>
</body>
</html>
