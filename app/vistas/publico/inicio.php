<?php $estilos = ['inicio']; ?>
<!doctype html>
<html lang="es">
<head>
    <?php require __DIR__ . '/../base/cabeza.php'; ?>
</head>
<body>
    <header class="header header--sidebar">
        <div class="header__top">
            <img class="header__logo" src="/img/bramajo-logo.png" alt="bramajo logo" />
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
        <h1>Bienvenid@ a BRAMAJO</h1>
        <p>BRAMAJO es una plataforma de gestion de torneos deportivos que permite a los organizadores crear y administrar torneos de manera eficiente. Los participantes pueden registrarse, consultar informacion sobre los torneos y mantenerse actualizados.</p>

        <div class="cards">
            <?php foreach ($torneos as $torneo): ?>
                <div class="card" onclick="window.location.href='/torneo?id=<?= $torneo['id_torneo'] ?>'">
                    <img src="<?= Torneo::imagen($torneo['disciplina']) ?>" alt="<?= e($torneo['nombre']) ?>" />
                    <div class="contenido-card">
                        <p class="card-activo">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14Zm3.844-8.791a.75.75 0 0 0-1.188-.918l-3.7 4.79-1.649-1.833a.75.75 0 1 0-1.114 1.004l2.25 2.5a.75.75 0 0 0 1.15-.043l4.25-5.5Z" clip-rule="evenodd"/></svg>
                            Activo ahora
                        </p>
                        <h2><?= e($torneo['nombre']) ?></h2>
                        <p class="card-sede">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= e($torneo['sede']) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="btn-torneo" onclick="window.location.href='/torneos'">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
            Ver todos los torneos
        </button>

        <?php require __DIR__ . '/../base/pie.php'; ?>
    </main>

    <script>
        document.getElementById('navToggle').addEventListener('click', function () {
            document.getElementById('headerNav').classList.toggle('abierto');
        });
    </script>
</body>
</html>