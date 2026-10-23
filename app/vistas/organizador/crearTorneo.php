<?php
$estilos = ['crearTorneo'];
$editando = isset($torneo['id_torneo']);
$valor = fn($campo) => e((string) ($torneo[$campo] ?? ''));
$error = fn($campo) => isset($errores[$campo]) ? '<small class="error">' . e($errores[$campo]) . '</small>' : '';
?>
<!doctype html>
<html lang="es">
	<head>
		<?php require __DIR__ . '/../base/cabeza.php'; ?>
	</head>
	<body>
		<header class="header">
			<img class="header__logo" src="/img/bramajo-logo.png" alt="BRAMAJO">
			<nav class="header__nav">
				<a class="nav__link" href="<?= $editando ? '/gestion/torneo?id=' . $torneo['id_torneo'] : '/gestion' ?>"><- Volver</a>
				<a class="nav__link" href="/soporte">Soporte</a>
			</nav>
		</header>

		<main>
			<section class="titulo">
				<h1><?= $editando ? 'EDITA TU TORNEO' : 'CREA TU TORNEO' ?></h1>
				<p>Completa los datos para crear y configurar tu torneo deportivo</p>
			</section>

		<form class="formulario" id="formCrearTorneo" method="post" action="<?= $editando ? '/gestion/editar?id=' . $torneo['id_torneo'] : '/gestion/nuevo' ?>">
			<?= csrf() ?>
			<section class="bloque">
				<h2>Información básica</h2>
				<div class="fila">
					<div class="campo">
						<label for="nombre">Nombre del Torneo</label>
						<input type="text" id="nombre" name="nombre" placeholder="Ej: Copa verano 2026" value="<?= $valor('nombre') ?>" maxlength="120" required>
						<?= $error('nombre') ?>
					</div>
					<div class="campo">
						<label for="disciplina">Deporte</label>
						<select id="disciplina" name="disciplina" required>
							<?= opciones(array_combine(Torneo::DISCIPLINAS, Torneo::DISCIPLINAS), $torneo['disciplina'] ?? '') ?>
						</select>
						<?= $error('disciplina') ?>
					</div>
				</div>

				<div class="campo campo-descripcion">
					<label for="descripcion">Descripción del Torneo</label>
					<textarea id="descripcion" name="descripcion" maxlength="500" placeholder="Escribí una breve descripción de lo que es el torneo o de lo que quieras..." required><?= $valor('descripcion') ?></textarea>
					<?= $error('descripcion') ?>
				</div>

					<div class="campo">
						<label for="id_modulo">Sistema de emparejamiento</label>
						<select id="id_modulo" name="id_modulo" required>
							<?= opciones(array_column($modulos, 'nombre', 'id_modulo'), $torneo['id_modulo'] ?? '') ?>
						</select>
						<?= $error('id_modulo') ?>
					</div>

			</section>
			<section class="bloque">
				<h2>Equipos y participantes</h2>
				<div class="fila equipos">
					<div class="campo">
						<label for="max_participantes">Cantidad de participantes</label>
						<div class="input-icono">
							<svg viewBox="0 0 24 24" aria-hidden="true">
								<circle cx="9" cy="8" r="3"></circle>
								<path d="M3 19c0-3 2.5-5 6-5s6 2 6 5"></path>
								<circle cx="17" cy="9" r="2"></circle>
								<path d="M15 15c3 .2 5 1.7 5 4"></path>
							</svg>
							<input type="number" id="max_participantes" name="max_participantes" placeholder="Ej: 16" min="2" max="128" value="<?= $valor('max_participantes') ?>" required>
						</div>
						<?= $error('max_participantes') ?>
					</div>
					<div class="campo">
						<label for="modalidad">Compiten</label>
						<div class="input-icono">
							<svg viewBox="0 0 24 24" aria-hidden="true">
								<circle cx="9" cy="8" r="3"></circle>
								<path d="M3 19c0-3 2.5-5 6-5s6 2 6 5"></path>
							</svg>
							<select id="modalidad" name="modalidad" required>
								<?= opciones(['INDIVIDUAL' => 'Jugadores individuales', 'EQUIPO' => 'Equipos'], $torneo['modalidad'] ?? '') ?>
							</select>
						</div>
					</div>
				</div>
			</section>

			<section class="bloque">
				<h2>Fechas</h2>

				<div class="fila fechas">
					<div class="campo">
						<label for="fecha_inicio">Fecha de inicio</label>
						<div class="input-icono">
							<svg viewBox="0 0 24 24" aria-hidden="true">
								<rect x="3" y="5" width="18" height="16" rx="2"></rect>
								<path d="M16 3v4M8 3v4M3 10h18"></path>
							</svg>
							<input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= $valor('fecha_inicio') ?>" required>
						</div>
						<?= $error('fecha_inicio') ?>
					</div>

					<div class="campo">
						<label for="fecha_fin">Fecha de finalización</label>
						<div class="input-icono">
							<svg viewBox="0 0 24 24" aria-hidden="true">
								<rect x="3" y="5" width="18" height="16" rx="2"></rect>
								<path d="M16 3v4M8 3v4M3 10h18"></path>
							</svg>
							<input type="date" id="fecha_fin" name="fecha_fin" value="<?= $valor('fecha_fin') ?>">
						</div>
						<?= $error('fecha_fin') ?>
					</div>
				</div>
			</section>

			<section class="bloque">
				<h2>Sede</h2>

				<div class="fila">
					<div class="campo">
						<label for="ubicacion">Ubicación</label>
						<div class="input-icono">
							<svg viewBox="0 0 24 24" aria-hidden="true">
								<path d="M12 21s7-6.2 7-12A7 7 0 0 0 5 9c0 5.8 7 12 7 12z"></path>
								<circle cx="12" cy="9" r="2.5"></circle>
							</svg>
							<input type="text" id="ubicacion" name="ubicacion" placeholder="Ej: Ciudad, País" value="<?= $valor('ubicacion') ?>" maxlength="120" required>
						</div>
						<?= $error('ubicacion') ?>
					</div>

					<div class="campo">
						<label for="sede">Sede</label>
						<div class="input-icono">
							<svg viewBox="0 0 24 24" aria-hidden="true">
								<path d="M12 21s7-6.2 7-12A7 7 0 0 0 5 9c0 5.8 7 12 7 12z"></path>
								<circle cx="12" cy="9" r="2.5"></circle>
							</svg>
							<input type="text" id="sede" name="sede" placeholder="Ej: Nombre de club deportivo" value="<?= $valor('sede') ?>" maxlength="120">
						</div>
					</div>
				</div>
			</section>

			<section class="bloque configuracion">
				<h2>Configuración</h2>

				<div class="fila">
					<div class="campo">
						<label for="puntos_victoria">Puntos por victoria</label>
						<input type="number" id="puntos_victoria" name="puntos_victoria" min="1" max="10" value="<?= $valor('puntos_victoria') ?>" required>
						<?= $error('puntos_victoria') ?>
					</div>
					<div class="campo">
						<label for="puntos_empate">Puntos por empate</label>
						<input type="number" id="puntos_empate" name="puntos_empate" min="0" max="10" value="<?= $valor('puntos_empate') ?>" required>
					</div>
				</div>
				<div class="fila">
					<div class="campo">
						<label for="puntos_derrota">Puntos por derrota</label>
						<input type="number" id="puntos_derrota" name="puntos_derrota" min="0" max="10" value="<?= $valor('puntos_derrota') ?>" required>
					</div>
					<div class="campo">
						<label for="cantidad_rondas">Rondas (solo sistema suizo)</label>
						<input type="number" id="cantidad_rondas" name="cantidad_rondas" min="1" max="15" value="<?= $valor('cantidad_rondas') ?>">
						<?= $error('cantidad_rondas') ?>
					</div>
				</div>

				<div class="campo">
					<label for="reglas">Reglas de equipo</label>
					<textarea id="reglas" name="reglas" maxlength="500" placeholder="Agrega reglas especiales, formato de puntos, etc."><?= $valor('reglas') ?></textarea>
				</div>
			</section>

			<button class="btn-crear" type="submit"><?= $editando ? 'GUARDAR CAMBIOS' : 'CREAR TORNEO' ?></button>
		</form>
	</main>
</body>
</html>
