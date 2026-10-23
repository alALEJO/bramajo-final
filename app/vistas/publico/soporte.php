<?php $estilos = ['soporte']; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require __DIR__ . '/../base/cabeza.php'; ?>
</head>
<body>
    <header class="header">
        <div class="header__top">
            <img class="header__logo" src="/img/bramajo-logo.png" alt="bramajo logo" />
            <button class="nav-toggle" id="navToggle" aria-label="Abrir menu">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
        <nav class="header__nav" id="headerNav">
            <a class="nav__link" href="/inicio">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Volver
            </a>
        </nav>
    </header>

    <main>
        <h1>Soporte</h1>
        <h2>Preguntas Frecuentes</h2>

        <section class="seccionUno">
            <p>Como registro un torneo?</p>
            <p>Para registrar un torneo, primero debes iniciar sesion en tu cuenta de organizador. Luego, dirigite a la seccion "Crear Torneo" y completa el formulario con los detalles del torneo, incluyendo nombre, fecha, reglas y premios.</p>
        </section>

        <section class="seccionDos">
            <p>Donde puedo encontrar mas informacion sobre los torneos?</p>
            <p>Puede encontrar mas informacion sobre los torneos en la seccion de "Torneos" de nuestra plataforma. Alli encontraras detalles sobre cada torneo, incluyendo reglas, fechas y premios.</p>
            <p>Y si quiere saber mas puede visitar el sitio web oficial de BRAMAJO o contactar con nuestro equipo de soporte a traves del correo electronico.</p>
        </section>

        <section class="seccionTres">
            <p>Como creo un torneo?</p>
            <p>Para crear un torneo, primero debes iniciar sesion en tu cuenta de organizador. Luego, dirigite a la seccion "Crear Torneo" y completa el formulario con los detalles del torneo, incluyendo nombre, fecha, reglas y premios. Una vez que hayas completado el formulario, haz clic en "Crear" para registrar tu torneo en la plataforma.</p>
        </section>
    </main>

    <script>
        document.getElementById('navToggle').addEventListener('click', function () {
            document.getElementById('headerNav').classList.toggle('abierto');
        });
    </script>
</body>
</html>