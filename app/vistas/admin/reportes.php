<?php $subtitulo = 'Reportes y configuración general del sistema'; require __DIR__ . '/../base/gestion_arriba.php'; ?>
<div class="formulario">
	<section class="bloque">
		<h2>Administración</h2>
		<p class="en-linea">
			<a class="boton-chico" href="/admin/usuarios">Usuarios</a>
			<a class="boton-chico" href="/admin/modulos">Módulos de competencia</a>
			<a class="boton-chico" href="/admin/auditoria">Auditoría</a>
		</p>
	</section>
	<?php foreach (['usuarios' => 'Usuarios por rol', 'torneos' => 'Torneos por estado', 'modulos' => 'Torneos por formato'] as $clave => $nombre): ?>
		<section class="bloque">
			<h2><?= $nombre ?></h2>
			<table class="tabla">
				<?php foreach ($resumen[$clave] as $dato => $cantidad): ?>
					<tr><td><?= e(Torneo::ESTADOS[$dato] ?? ucfirst(strtolower(str_replace('_', ' ', $dato)))) ?></td><td><strong><?= $cantidad ?></strong></td></tr>
				<?php endforeach; ?>
			</table>
		</section>
	<?php endforeach; ?>
	<section class="bloque">
		<h2>Actividad</h2>
		<table class="tabla">
			<tr><td>Partidos con resultado</td><td><strong><?= $resumen['partidos'] ?></strong></td></tr>
			<tr><td>Equipos registrados</td><td><strong><?= $resumen['equipos'] ?></strong></td></tr>
		</table>
	</section>
</div>
<?php require __DIR__ . '/../base/gestion_abajo.php'; ?>
