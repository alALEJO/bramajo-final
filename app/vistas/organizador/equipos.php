<?php $subtitulo = 'Equipos que se pueden inscribir en los torneos por equipos'; require __DIR__ . '/../base/gestion_arriba.php'; ?>
<div class="formulario">
	<form class="bloque" method="post" action="/equipos">
		<?= csrf() ?>
		<h2>Nuevo equipo</h2>
		<div class="campo">
			<label for="nombre">Nombre del equipo</label>
			<input type="text" id="nombre" name="nombre" maxlength="80" value="<?= e($_POST['nombre'] ?? '') ?>" required>
			<?php if (isset($errores['nombre'])): ?><small class="error"><?= e($errores['nombre']) ?></small><?php endif; ?>
		</div>
		<p><button class="boton-chico">Crear equipo</button></p>
	</form>
	<section class="bloque">
		<h2>Equipos registrados (<?= count($equipos) ?>)</h2>
		<div class="tabla-scroll">
			<table class="tabla">
				<tr><th>Equipo</th><th>Integrantes</th></tr>
				<?php foreach ($equipos as $equipo): ?>
					<tr><td><a href="/equipo?id=<?= $equipo['id_equipo'] ?>"><?= e($equipo['nombre']) ?></a></td><td><?= $equipo['integrantes'] ?></td></tr>
				<?php endforeach; ?>
			</table>
		</div>
	</section>
</div>
<?php require __DIR__ . '/../base/gestion_abajo.php'; ?>
