<?php
$subtitulo = 'Alta, suspensión y desbloqueo de cuentas';
$error = fn($campo) => isset($errores[$campo]) ? '<small class="error">' . e($errores[$campo]) . '</small>' : '';
require __DIR__ . '/../base/gestion_arriba.php';
?>
<div class="formulario">
	<form class="bloque" method="post" action="/admin/usuarios">
		<?= csrf() ?>
		<h2>Nuevo usuario</h2>
		<div class="fila">
			<div class="campo"><label for="nombre">Nombre</label><input type="text" id="nombre" name="nombre" value="<?= e($datos['nombre'] ?? '') ?>" maxlength="60" required><?= $error('nombre') ?></div>
			<div class="campo"><label for="apellido">Apellido</label><input type="text" id="apellido" name="apellido" value="<?= e($datos['apellido'] ?? '') ?>" maxlength="60" required><?= $error('apellido') ?></div>
		</div>
		<div class="fila">
			<div class="campo"><label for="email">Correo</label><input type="email" id="email" name="email" value="<?= e($datos['email'] ?? '') ?>" maxlength="150" required><?= $error('email') ?></div>
			<div class="campo"><label for="password">Contraseña inicial</label><input type="password" id="password" name="password" minlength="10" maxlength="72" required><?= $error('password') ?></div>
		</div>
		<div class="campo">
			<label for="rol">Rol</label>
			<select id="rol" name="rol"><?= opciones(['ORGANIZADOR' => 'Organizador', 'ADMIN_GENERAL' => 'Administrador general', 'PARTICIPANTE' => 'Participante'], $datos['rol'] ?? '') ?></select>
		</div>
		<p><button class="boton-chico">Crear usuario</button></p>
	</form>

	<section class="bloque">
		<h2>Usuarios (<?= count($usuarios) ?>)</h2>
		<form method="get" class="en-linea">
			<input type="search" name="buscar" placeholder="Buscar por nombre o correo" value="<?= e($buscar) ?>">
			<button class="boton-chico">Buscar</button>
		</form>
		<div class="tabla-scroll">
			<table class="tabla">
				<tr><th>Usuario</th><th>Rol</th><th>Estado</th><th></th></tr>
				<?php foreach ($usuarios as $u): ?>
					<?php $bloqueado = Usuario::estaBloqueado($u); ?>
					<tr>
						<td><?= e($u['nombre'] . ' ' . $u['apellido']) ?><br><small><?= e($u['email']) ?></small></td>
						<td><?= e(ucfirst(strtolower(str_replace('_', ' ', $u['rol'])))) ?></td>
						<td><?= $bloqueado ? 'Bloqueado' : ucfirst(strtolower($u['estado'])) ?></td>
						<td>
							<?php if ($u['id_usuario'] != $usuario['id']): ?>
								<form method="post" action="/admin/estado"><?= csrf() ?><input type="hidden" name="id" value="<?= $u['id_usuario'] ?>">
									<?php if ($u['estado'] === 'ACTIVO' && !$bloqueado): ?>
										<button class="boton-chico peligro" name="accion" value="suspender">Suspender</button>
									<?php else: ?>
										<button class="boton-chico" name="accion" value="activar"><?= $bloqueado ? 'Desbloquear' : 'Activar' ?></button>
									<?php endif; ?>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</section>
</div>
<?php require __DIR__ . '/../base/gestion_abajo.php'; ?>
