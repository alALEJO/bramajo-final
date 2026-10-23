<?php
$subtitulo = 'Últimos 200 movimientos registrados';
$tablas = ['usuario', 'torneo', 'organizador_torneo', 'equipo', 'integrante_equipo', 'participante', 'ronda', 'enfrentamiento', 'modulo_competencia'];
$acciones = ['LOGIN', 'LOGIN_FALLIDO', 'ALTA', 'MODIFICACION', 'BAJA'];
require __DIR__ . '/../base/gestion_arriba.php';
?>
<div class="formulario">
	<section class="bloque">
		<form method="get" class="en-linea">
			<select name="tabla"><option value="">Todas las tablas</option><?= opciones(array_combine($tablas, $tablas), $tabla) ?></select>
			<select name="accion"><option value="">Todas las acciones</option><?= opciones(array_combine($acciones, $acciones), $accion) ?></select>
			<button class="boton-chico">Filtrar</button>
		</form>
		<div class="tabla-scroll">
			<table class="tabla">
				<tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>Antes</th><th>Después</th><th>IP</th></tr>
				<?php foreach ($registros as $r): ?>
					<tr>
						<td><?= fecha($r['fecha_hora'], true) ?></td><td><?= e($r['usuario'] ?? '-') ?></td><td><?= e($r['accion']) ?></td>
						<td><?= e($r['tabla_afectada']) ?> #<?= $r['id_registro'] ?></td>
						<td><small><?= e($r['valor_anterior']) ?></small></td><td><small><?= e($r['valor_nuevo']) ?></small></td><td><?= e($r['ip']) ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</section>
</div>
<?php require __DIR__ . '/../base/gestion_abajo.php'; ?>
