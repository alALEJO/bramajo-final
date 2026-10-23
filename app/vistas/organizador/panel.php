<?php
$estilos = ['infoTorneos'];
$gestion = true;
$enlace = fn($p) => "/gestion/torneo?id={$torneo['id_torneo']}&pestana=$p";
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
            <a class="nav__link" href="/gestion">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Mis torneos
            </a>
            <?php if ($torneo['estado'] !== 'BORRADOR'): ?>
                <a class="nav__link" href="/torneo?id=<?= $torneo['id_torneo'] ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    Ver como público
                </a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="contenido-principal">
        <?php require __DIR__ . '/../base/mensaje.php'; ?>
        <section class="encabezado-torneo">
            <img class="encabezado-torneo__imagen" src="<?= Torneo::imagen($torneo['disciplina']) ?>" alt="<?= e($torneo['nombre']) ?>">
            <div class="encabezado-torneo__informacion">
                <p class="estado-torneo"><?= Torneo::ESTADOS[$torneo['estado']] ?></p>
                <h1><?= e($torneo['nombre']) ?></h1>
                <p class="descripcion-torneo"><?= e($torneo['disciplina']) ?> · <?= e($torneo['modulo_nombre']) ?> · <?= $torneo['inscriptos'] ?>/<?= $torneo['max_participantes'] ?> inscriptos</p>
                <div class="en-linea">
                    <?php if (in_array($torneo['estado'], ['BORRADOR', 'INSCRIPCION'], true)): ?>
                        <a class="boton-chico" href="/gestion/editar?id=<?= $torneo['id_torneo'] ?>">Editar</a>
                    <?php endif; ?>
                    <?php if ($torneo['estado'] === 'BORRADOR'): ?>
                        <form method="post" action="/gestion/estado"><?= csrf() ?><input type="hidden" name="id" value="<?= $torneo['id_torneo'] ?>">
                            <button class="boton-chico" name="accion" value="abrir">Abrir inscripciones</button></form>
                    <?php elseif ($torneo['estado'] === 'INSCRIPCION' && !$rondas): ?>
                        <form method="post" action="/gestion/estado"><?= csrf() ?><input type="hidden" name="id" value="<?= $torneo['id_torneo'] ?>">
                            <button class="boton-chico" name="accion" value="borrador">Volver a borrador</button></form>
                    <?php endif; ?>
                </div>
                <?php if ($campeon): ?>
                    <p class="estado-torneo">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true" style="color:var(--color-warning);margin-right:4px;"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                        Campeón: <?= e($nombres[$campeon] ?? '') ?>
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <nav class="pestanas">
            <a class="pestana <?= $pestana === 'inscripciones' ? 'activa' : '' ?>" href="<?= $enlace('inscripciones') ?>">Inscripciones</a>
            <a class="pestana <?= $pestana === 'rondas' ? 'activa' : '' ?>" href="<?= $enlace('rondas') ?>">Rondas y resultados</a>
            <?php if ($usuario['rol'] === 'ADMIN_GENERAL'): ?>
                <a class="pestana <?= $pestana === 'organizadores' ? 'activa' : '' ?>" href="<?= $enlace('organizadores') ?>">Organizadores</a>
            <?php endif; ?>
        </nav>

        <section class="contenido-pestana">
            <?php if ($pestana === 'inscripciones'): ?>
                <h2>Inscripciones</h2>
                <?php if ($torneo['estado'] === 'INSCRIPCION'): ?>
                    <form method="post" action="/gestion/inscribir" class="en-linea">
                        <?= csrf() ?>
                        <input type="hidden" name="id" value="<?= $torneo['id_torneo'] ?>">
                        <select name="candidato" required>
                            <option value="">Elegí <?= $torneo['modalidad'] === 'EQUIPO' ? 'un equipo' : 'un participante' ?></option>
                            <?php foreach ($candidatos as $c): ?>
                                <option value="<?= $c['id_equipo'] ?? $c['id_usuario'] ?>"><?= e(isset($c['id_equipo']) ? $c['nombre'] : $c['apellido'] . ', ' . $c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="boton-chico">Inscribir</button>
                    </form>
                <?php else: ?>
                    <p class="mensaje-vacio">Las inscripciones se gestionan mientras el torneo está en «Inscripciones abiertas».</p>
                <?php endif; ?>
                <div class="participantes-lista">
                    <?php foreach ($participantes as $numero => $p): ?>
                        <article class="participante">
                            <span class="participante__numero"><?= $numero + 1 ?></span>
                            <p><?= e($p['nombre']) ?> · <?= ucfirst(strtolower($p['estado'])) ?></p>
                            <?php if ($torneo['estado'] === 'INSCRIPCION' && $p['estado'] === 'PENDIENTE'): ?>
                                <form method="post" action="/gestion/inscripcion" class="en-linea">
                                    <?= csrf() ?>
                                    <input type="hidden" name="id_participante" value="<?= $p['id_participante'] ?>">
                                    <button class="boton-chico" name="estado" value="INSCRIPTO">Aceptar</button>
                                    <button class="boton-chico peligro" name="estado" value="RECHAZADO">Rechazar</button>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php if (!$participantes): ?><p class="mensaje-vacio">Todavía no hay inscripciones.</p><?php endif; ?>

            <?php elseif ($pestana === 'rondas'): ?>
                <h2>Rondas y resultados</h2>
                <?php if (in_array($torneo['estado'], ['INSCRIPCION', 'EN_CURSO'], true)): ?>
                    <form method="post" action="/gestion/ronda" class="en-linea">
                        <?= csrf() ?>
                        <input type="hidden" name="id" value="<?= $torneo['id_torneo'] ?>">
                        <button class="boton-chico">Generar próxima ronda</button>
                        <span>El sistema arma los cruces según el formato.<?= $torneo['estado'] === 'INSCRIPCION' ? ' Al generar la primera ronda se cierran las inscripciones.' : '' ?></span>
                    </form>
                <?php endif; ?>
                <?php if (!$rondas): ?><p class="mensaje-vacio">Todavía no hay rondas.</p><?php endif; ?>
                <?php foreach (array_reverse($rondas) as $ronda): ?>
                    <h3 id="ronda-<?= $ronda['id_ronda'] ?>"><?= e($ronda['nombre']) ?> · <?= $ronda['estado'] === 'CERRADA' ? 'cerrada' : 'abierta' ?></h3>
                    <div class="resultados-lista">
                        <?php foreach ($ronda['cruces'] as $cruce): ?>
                            <?php require __DIR__ . '/../publico/_cruce.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($ronda['estado'] === 'ABIERTA'): ?>
                        <form method="post" action="/gestion/cerrar" class="en-linea">
                            <?= csrf() ?>
                            <input type="hidden" name="id" value="<?= $ronda['id_ronda'] ?>">
                            <button class="boton-chico">Cerrar y publicar ronda</button>
                        </form>
                    <?php endif; ?>
                <?php endforeach; ?>

            <?php else: ?>
                <h2>Organizadores asignados</h2>
                <?php foreach ($organizadores as $o): ?>
                    <form method="post" action="/gestion/organizador" class="en-linea">
                        <?= csrf() ?>
                        <input type="hidden" name="id" value="<?= $torneo['id_torneo'] ?>">
                        <input type="hidden" name="id_usuario" value="<?= $o['id_usuario'] ?>">
                        <span style="flex: 1"><?= e($o['nombre'] . ' ' . $o['apellido']) ?><?= $o['puede_corregir'] ? ' · puede corregir resultados' : '' ?></span>
                        <button class="boton-chico peligro" name="accion" value="quitar">Quitar</button>
                    </form>
                <?php endforeach; ?>
                <form method="post" action="/gestion/organizador" class="en-linea">
                    <?= csrf() ?>
                    <input type="hidden" name="id" value="<?= $torneo['id_torneo'] ?>">
                    <select name="id_usuario">
                        <?php foreach ($posiblesOrganizadores as $o): ?>
                            <option value="<?= $o['id_usuario'] ?>"><?= e($o['nombre'] . ' ' . $o['apellido']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label><input type="checkbox" name="puede_corregir"> Puede corregir resultados</label>
                    <button class="boton-chico">Asignar</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
    <?php require __DIR__ . '/../base/pie.php'; ?>
    <script src="/js/confirmar.js"></script>
    <script>
        const navToggle = document.getElementById('navToggle');
        if (navToggle) {
            navToggle.addEventListener('click', function () {
                document.getElementById('headerNav').classList.toggle('abierto');
            });
        }
    </script>
</body>
</html>
