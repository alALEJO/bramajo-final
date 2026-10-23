<?php $subtitulo = 'Los torneos que podés gestionar'; require __DIR__ . '/../base/gestion_arriba.php'; ?>
<div class="formulario">
	<section class="bloque">
		<h2>Torneos (<?= count($torneos) ?>)</h2>
		<div class="tabla-scroll">
			<table class="tabla">
				<tr><th>Torneo</th><th>Formato</th><th>Inscriptos</th><th>Estado</th></tr>
				<?php foreach ($torneos as $t): ?>
					<tr>
						<td><a href="/gestion/torneo?id=<?= $t['id_torneo'] ?>"><?= e($t['nombre']) ?></a></td>
						<td><?= e($t['modulo_nombre']) ?></td>
						<td><?= $t['inscriptos'] ?>/<?= $t['max_participantes'] ?></td>
						<td><?= Torneo::ESTADOS[$t['estado']] ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<?php if (!$torneos): ?><p>Todavía no tenés torneos asignados.</p><?php endif; ?>
	</section>
	<button class="btn-crear" type="button" onclick="window.location.href='/gestion/nuevo'">CREAR TORNEO</button>
</div>
<?php require __DIR__ . '/../base/gestion_abajo.php'; ?>
