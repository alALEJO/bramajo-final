<?php $subtitulo = 'Un módulo deshabilitado no se ofrece al crear torneos. Los torneos que ya lo usan siguen funcionando.'; require __DIR__ . '/../base/gestion_arriba.php'; ?>
<div class="formulario">
	<?php foreach ($modulos as $m): ?>
		<form class="bloque" method="post" action="/admin/modulos">
			<?= csrf() ?>
			<input type="hidden" name="id" value="<?= $m['id_modulo'] ?>">
			<h2><?= e($m['nombre']) ?> · <?= $m['activo'] ? 'habilitado' : 'deshabilitado' ?></h2>
			<p><?= e($m['descripcion']) ?></p>
			<p><button class="boton-chico <?= $m['activo'] ? 'peligro' : '' ?>"><?= $m['activo'] ? 'Deshabilitar' : 'Habilitar' ?></button></p>
		</form>
	<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../base/gestion_abajo.php'; ?>
