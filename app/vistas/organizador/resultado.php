<?php $subtitulo = $torneo['nombre'] . ' · ' . $cruce['ronda']; require __DIR__ . '/../base/gestion_arriba.php'; ?>
<form class="formulario" method="post" action="/gestion/resultado?id=<?= $cruce['id_enfrentamiento'] ?>">
	<?= csrf() ?>
	<section class="bloque">
		<h2><?= $cruce['estado_ronda'] === 'CERRADA' ? 'Corregir resultado' : 'Cargar resultado' ?></h2>
		<?php if ($cruce['estado_ronda'] === 'CERRADA'): ?>
			<p class="mensaje error">La ronda está cerrada. El cambio queda en la auditoría con el valor anterior y el nuevo.</p>
		<?php endif; ?>
		<div class="fila">
			<?php foreach (['local', 'visitante'] as $lado): ?>
				<div class="campo">
					<label for="puntos_<?= $lado ?>"><?= e($nombres[$cruce["id_$lado"]]) ?></label>
					<input type="number" id="puntos_<?= $lado ?>" name="puntos_<?= $lado ?>" min="0" max="999" value="<?= e((string) $cruce["puntos_$lado"]) ?>" required>
					<?php if (isset($errores["puntos_$lado"])): ?><small class="error"><?= e($errores["puntos_$lado"]) ?></small><?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<button class="btn-crear" type="submit">GUARDAR RESULTADO</button>
	<p><a href="/gestion/torneo?id=<?= $torneo['id_torneo'] ?>&pestana=rondas">Cancelar</a></p>
</form>
<?php require __DIR__ . '/../base/gestion_abajo.php'; ?>
