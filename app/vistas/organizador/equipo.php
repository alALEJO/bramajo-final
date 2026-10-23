<?php $subtitulo = 'Integrantes del equipo'; require __DIR__ . '/../base/gestion_arriba.php'; ?>
<div class="formulario">
	<section class="bloque">
		<h2>Integrantes (<?= count($integrantes) ?>)</h2>
		<?php foreach ($integrantes as $integrante): ?>
			<form method="post" action="/equipo" class="en-linea">
				<?= csrf() ?>
				<input type="hidden" name="id" value="<?= $equipo['id_equipo'] ?>">
				<input type="hidden" name="id_usuario" value="<?= $integrante['id_usuario'] ?>">
				<span style="flex: 1"><?= e($integrante['nombre'] . ' ' . $integrante['apellido']) ?></span>
				<button class="boton-chico peligro" name="accion" value="quitar">Quitar</button>
			</form>
		<?php endforeach; ?>
		<?php if (!$integrantes): ?><p>El equipo todavía no tiene integrantes.</p><?php endif; ?>
		<form method="post" action="/equipo" class="en-linea">
			<?= csrf() ?>
			<input type="hidden" name="id" value="<?= $equipo['id_equipo'] ?>">
			<select name="id_usuario" required>
				<option value="">Elegí un participante</option>
				<?php foreach ($jugadores as $jugador): ?>
					<option value="<?= $jugador['id_usuario'] ?>"><?= e($jugador['apellido'] . ', ' . $jugador['nombre']) ?></option>
				<?php endforeach; ?>
			</select>
			<button class="boton-chico">Agregar</button>
		</form>
	</section>
	<p><a href="/equipos">Volver a equipos</a></p>
</div>
<?php require __DIR__ . '/../base/gestion_abajo.php'; ?>
