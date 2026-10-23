<?php $estilos = ['login']; ?>
<!doctype html>
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    Continuar como invitado
                </a>
            </nav>
        </header>

        <main>
            <?php require __DIR__ . '/../base/mensaje.php'; ?>
            <h1 class="gestion_titulo">INICIAR SESION</h1>
            <p class="gestion_login">Completa los datos para iniciar sesion.</p>

            <form class="formulario" id="formIniciarS" method="post" action="/login">
                <?= csrf() ?>
                <p id="mensajeError"><?= e($error ?? '') ?></p>

                <div class="datos">
                    <label for="correo">Correo</label>
                    <input type="email" id="correo" name="correo" placeholder="Ej: nombre@gmail.com" value="<?= e($correo ?? '') ?>" maxlength="150" required>
                </div>

                <div class="datos">
                    <label for="password">Contrasena</label>
                    <div class="input-password">
                        <input type="password" id="password" name="password" placeholder="Tu contrasena" maxlength="72" required>
                        <button type="button" id="mostrarPassword" aria-label="Mostrar contrasena">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button class="btn-iniciarS" type="submit">INICIAR SESION</button>

                <h3>
                    No tienes una cuenta?
                    <a id="Registrarse" href="/registro">Registrate aqui</a>
                </h3>
            </form>
        </main>

        <?php require __DIR__ . '/../base/pie.php'; ?>
        <script src="/js/login.js"></script>
        <script>
            document.getElementById('navToggle').addEventListener('click', function () {
                document.getElementById('headerNav').classList.toggle('abierto');
            });
        </script>
    </body>
</html>
