<?php
$estilos = ['perfil'];
$roles = ['ADMIN_GENERAL' => 'Administrador general', 'ORGANIZADOR' => 'Organizador de torneos', 'PARTICIPANTE' => 'Participante'];
$proximos = array_filter($torneos, fn($t) => $t['proximo']);
$error = fn($campo) => isset($errores[$campo]) ? '<small class="error">' . e($errores[$campo]) . '</small>' : '';
?>
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
		<div class="container">
			<section class="card">
				<img class="card__banner" src="/img/fondo-perfil.jpg" alt="Fondo de la carta" />
				<img class="card__profile" src="/img/foto-perfil.jpg" alt="Foto de perfil" />

				<p class="card__name"><?= e($datos['nombre'] . ' ' . $datos['apellido']) ?></p>
				<p class="card__job"><?= $roles[$datos['rol']] ?></p>

				<section class="card__info">
					<p><?= e($datos['email']) ?></p>
					<p><?= $datos['telefono_visible'] ? e($datos['telefono_visible']) : 'Sin teléfono' ?></p>
				</section>
				<div class="card__desc">
					<h2>Próximos partidos:</h2>
					<?php foreach (array_slice($proximos, 0, 3) as $t): ?>
						<p><?= fecha($t['proximo']['fecha_hora'], true) ?> · <?= e($t['nombre']) ?> vs. <?= e($t['proximo']['rival']) ?></p>
					<?php endforeach; ?>
					<?php if (!$proximos): ?><p>No tenés partidos pendientes.</p><?php endif; ?>
				</div>
				<section class="card__follow">
					<div class="follow__num">
						<div class="num">
							<p><?= count($torneos) ?></p>
							<p>Torneos</p>
						</div>
						<div class="num">
							<p><?= count($proximos) ?></p>
							<p>Por jugar</p>
						</div>
					</div>
					<button id="botonSeguir" onclick="window.location.href='#editar'">Editar</button>
				</section>
			</section>
		</div>

		<?php if ($torneos): ?>
		<section class="panel">
			<h2>Mis torneos</h2>
			<div class="tabla-scroll">
				<table class="tabla">
					<tr><th>Torneo</th><th>Estado</th><th>Posición</th></tr>
					<?php foreach ($torneos as $t): ?>
						<tr>
							<td><a href="/torneo?id=<?= $t['id_torneo'] ?>"><?= e($t['nombre']) ?></a></td>
							<td><?= $t['inscripcion'] === 'INSCRIPTO' ? Torneo::ESTADOS[$t['estado']] : ucfirst(strtolower($t['inscripcion'])) ?></td>
							<td><?= $t['posicion'] && $t['modulo'] !== 'ELIMINACION' ? $t['posicion'] . '.º' : '-' ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</section>
		<?php endif; ?>

		<section class="panel" id="editar">
			<h2>Mis datos</h2>
			<form method="post" action="/perfil">
				<?= csrf() ?>
				<label for="nombre">Nombre</label>
				<input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre']) ?>" maxlength="60" required><?= $error('nombre') ?>
				<label for="apellido">Apellido</label>
				<input type="text" id="apellido" name="apellido" value="<?= e($datos['apellido']) ?>" maxlength="60" required><?= $error('apellido') ?>
				<label for="telefono">Teléfono</label>
				<input type="tel" id="telefono" name="telefono" value="<?= e($datos['telefono_visible']) ?>" maxlength="20"><?= $error('telefono') ?>
				<button class="boton-chico">Guardar</button>
			</form>

			<h2>Cambiar contraseña</h2>
			<form method="post" action="/perfil/password">
				<?= csrf() ?>
				<label for="actual">Contraseña actual</label>
				<input type="password" id="actual" name="actual" required><?= $error('actual') ?>
				<label for="nueva">Contraseña nueva</label>
				<input type="password" id="nueva" name="nueva" minlength="10" maxlength="72" required><?= $error('nueva') ?>
				<button class="boton-chico">Cambiar</button>
			</form>
		</section>
		</main>

		<?php require __DIR__ . '/../base/pie.php'; ?>
		<script>
			document.getElementById('navToggle').addEventListener('click', function () {
				document.getElementById('headerNav').classList.toggle('abierto');
			});
		</script>
	</body>
</html>
