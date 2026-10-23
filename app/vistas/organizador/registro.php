<?php
$estilos = ['registro'];
$error = fn($campo) => isset($errores[$campo]) ? '<small class="error">' . e($errores[$campo]) . '</small>' : '';
?>
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
		<h1 class="gestion_titulo">REGISTRARSE</h1>
		<p class="gestion_registro">
		Completa los datos y campos obligatorios para un registro exitoso.
    	</p>
			<form class="formulario" id="formRegistro" method="post" action="/registro">
				<?= csrf() ?>
				<p id="mensajeError"></p>
				<div class="datos">
					<label for="nombre">Nombre</label>
					<input type="text" id="nombre" name="nombre" placeholder="Ej: Lucía" value="<?= e($datos['nombre'] ?? '') ?>" maxlength="60" required>
					<?= $error('nombre') ?>
				</div>
				<div class="datos">
					<label for="apellido">Apellido</label>
					<input type="text" id="apellido" name="apellido" placeholder="Ej: Rodríguez" value="<?= e($datos['apellido'] ?? '') ?>" maxlength="60" required>
					<?= $error('apellido') ?>
				</div>
				<div class="datos">
					<label for="correo">Correo</label>
					<input type="email" id="correo" name="correo" placeholder="Ej: Nombre1234@gmail.com" value="<?= e($datos['correo'] ?? '') ?>" maxlength="150" required>
					<?= $error('correo') ?>
				</div>
				<div class="datos">
					<label for="telefono">Teléfono (opcional)</label>
					<input type="tel" id="telefono" name="telefono" placeholder="Ej: 099123456" value="<?= e($datos['telefono'] ?? '') ?>" maxlength="20">
					<?= $error('telefono') ?>
				</div>
				<div class="datos">
					<label for="password">Contraseña</label>
					<div class="input-password">
						<input type="password" id="password" name="password" placeholder="Mínimo 10, con mayúscula, minúscula y número" minlength="10" maxlength="72" required>
						<button type="button" id="mostrarPassword" aria-label="Mostrar contraseña">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="20" height="20"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
						</button>
					</div>
					<?= $error('password') ?>
				</div>
				<div class="datos">
					<label for="confirmar">Confirmar contraseña</label>
					<div class="input-password">
						<input type="password" id="confirmar" name="confirmar" placeholder="Repetí la contraseña" minlength="10" maxlength="72" required>
					</div>
					<?= $error('confirmar') ?>
				</div>
				<button class="btn-registrar" type="submit">
					REGISTRAR
				</button>
				<h3>
					¿Ya tienes cuenta?
					<a id="iniciarSesion" href="/login">Inicia sesión aquí</a>
				</h3>
			</form>
		</main>
		<?php require __DIR__ . '/../base/pie.php'; ?>
		<script src="/js/registro.js"></script>
		<script>
			document.getElementById('navToggle').addEventListener('click', function () {
				document.getElementById('headerNav').classList.toggle('abierto');
			});
		</script>
	</body>
</html>
