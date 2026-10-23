<?php
$estilos = ['torneos'];
$puedeUnirse = $usuario && $usuario['rol'] === 'PARTICIPANTE';
?>
<!doctype html>
<html lang="es">
<head>
    <?php require __DIR__ . '/../base/cabeza.php'; ?>
</head>
<body>
    <header class="header header--sidebar">
        <div class="header__top">
            <img class="header__logo" src="/img/bramajo-logo.png" alt="bramajo logo">
            <button class="nav-toggle" id="navToggle" aria-label="Abrir menu">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
        <nav class="header__nav" id="headerNav">
            <?php require __DIR__ . '/../base/menu.php'; ?>
        </nav>
    </header>

    <main class="con-sidebar">
        <?php require __DIR__ . '/../base/mensaje.php'; ?>
        <h1>TORNEOS</h1>
        <form class="busqueda" method="get" action="/torneos">
            <input type="hidden" name="filtro" value="<?= $filtro ?>">
            <div class="buscador">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.04 6.04a7.5 7.5 0 0 0 10.61 10.61Z"/>
                </svg>
                <input type="text" placeholder="Buscar torneos..." id="buscador" name="buscar" value="<?= e($buscar) ?>">
            </div>
            <div class="filtros">
                <select id="deporte" name="deporte" onchange="this.form.submit()">
                    <option value="">Deporte</option>
                    <?= opciones(array_combine(Torneo::DISCIPLINAS, Torneo::DISCIPLINAS), $deporte) ?>
                </select>

                <button type="button" class="filtro <?= $filtro === "recientes" ? "activo" : "" ?>"
                    onclick="window.location.href='/torneos?filtro=recientes'">
                    Populares
                </button>

                <button type="button" class="filtro <?= $filtro === "abiertas" ? "activo" : "" ?>"
                    onclick="window.location.href='/torneos?filtro=abiertas'">
                    Inscripciones abiertas
                </button>
            </div>
        </form>

        <section class="<?= $filtro === "abiertas" ? "inscripcionabierta" : "popular" ?>">
            <h2 class="seccion-titulo"><?= $filtro === 'abiertas' ? 'Inscripciones abiertas' : 'Populares' ?></h2>

            <div class="torneos-grid">
                <?php foreach ($torneos as $torneo): ?>
                    <article class="torneo-card" onclick="window.location.href='/torneo?id=<?= $torneo['id_torneo'] ?>'">
                        <img src="<?= Torneo::imagen($torneo['disciplina']) ?>" alt="<?= e($torneo['nombre']) ?>">
                        <div class="torneo-info">
                            <p class="fecha">Fecha: <?= fecha($torneo['fecha_inicio']) ?></p>
                            <h3><?= e($torneo['nombre']) ?></h3>
                            <p class="ubicacion">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                <?= e($torneo['sede']) ?>
                            </p>
                            <?php if ($puedeUnirse && $torneo['estado'] === 'INSCRIPCION' && $torneo['modalidad'] === 'INDIVIDUAL'): ?>
                                <button class="btn-unirse" type="button" data-id="<?= $torneo['id_torneo'] ?>" data-nombre="<?= e($torneo['nombre']) ?>"
                                    onclick="event.stopPropagation(); mostrarventana(this);">
                                    Unirse
                                </button>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if (empty($torneos)): ?>
                <p class="sin-torneos">No hay torneos disponibles en esta seccion.</p>
            <?php endif; ?>
        </section>

        <section class="tipos">
            <h2 class="seccion-titulo">Tipos de deporte</h2>
            <div class="tipos-grid">
                <?php foreach (Torneo::DISCIPLINAS as $disciplina): ?>
                    <button class="tipo" onclick="window.location.href='/torneos?deporte=<?= urlencode($disciplina) ?>'">
                        <?= $disciplina ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php if ($usuario && $usuario['rol'] !== 'PARTICIPANTE'): ?>
                <button class="crear-torneo" onclick="window.location.href='/gestion/nuevo'">
                    ORGANIZA TUS PROPIOS TORNEOS
                </button>
            <?php endif; ?>
        </section>

        <?php require __DIR__ . '/../base/pie.php'; ?>
    </main>

    <?php if ($puedeUnirse): ?>
    <div class="ventana" id="ventana">
        <div class="ventana-contenido">
            <h2>SOLICITA UNIRTE</h2>
            <button class="cerrar-ventana" onclick="cerrarVentana()" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <form id="formulariounirse" method="post" action="/inscribirme">
                <?= csrf() ?>
                <input type="hidden" name="id" id="idTorneo">
                <label for="torneo">Torneo:</label>
                <input type="text" id="torneo" readonly>
                <label for="nombre">Nombre y Apellido:</label>
                <input type="text" id="nombre" value="<?= e($usuario['nombre']) ?>" readonly>
                <p style="font-size:0.82rem;color:var(--color-text-muted)">El organizador revisa tu solicitud y te acepta o la rechaza.</p>
                <button type="submit" class="btn-solicitud">Enviar solicitud</button>
            </form>
        </div>
    </div>
    <script src="/js/unirse.js"></script>
    <?php endif; ?>

    <script>
        document.getElementById('navToggle').addEventListener('click', function () {
            document.getElementById('headerNav').classList.toggle('abierto');
        });
    </script>
</body>
</html>
