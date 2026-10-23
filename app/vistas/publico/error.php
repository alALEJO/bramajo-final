<?php $estilos = ['soporte']; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require __DIR__ . '/../base/cabeza.php'; ?>
</head>
<body>
    <header class="header">
        <nav class="header__nav">
          <img class="header__logo" src="/img/bramajo-logo.png" alt="bramajo logo" />
          <a class="nav__link" href="/inicio"><- Volver</a>
        </nav>
    </header>
    <main>
        <h1><?= e($titulo) ?></h1>
        <section class="seccionUno">
            <p><?= e($texto) ?></p>
            <p><a class="boton-chico" href="/inicio">Volver al inicio</a></p>
        </section>
    </main>
</body>
</html>
