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
        <h1>Verificar acta</h1>
        <h2>Firma digital</h2>
        <section class="seccionUno">
            <p>Pegá el contenido del acta descargada. El sistema comprueba la firma con su clave pública: si alguien cambió un solo dato, la firma deja de coincidir.</p>
            <?php if ($resultado === true): ?><p class="mensaje exito">Firma válida: el acta es original.</p><?php endif; ?>
            <?php if ($resultado === false): ?><p class="mensaje error">Firma no válida: el acta fue modificada o no es de Bramajo.</p><?php endif; ?>
            <form method="post" action="/verificar">
                <?= csrf() ?>
                <textarea name="acta" rows="10" style="width: 100%" required></textarea>
                <p><button class="boton-chico">Verificar</button></p>
            </form>
        </section>
    </main>
</body>
</html>
