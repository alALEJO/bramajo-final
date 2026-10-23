<!doctype html>
<html lang="es">
	<head>
		<?php $estilos = ['crearTorneo']; require __DIR__ . '/cabeza.php'; ?>
	</head>
	<body>
		<header class="header header--sidebar">
			<div class="header__top">
				<img class="header__logo" src="/img/bramajo-logo.png" alt="BRAMAJO">
				<button class="nav-toggle" id="navToggle" aria-label="Abrir menu">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
				</button>
			</div>
			<nav class="header__nav" id="headerNav">
				<?php require __DIR__ . '/menu.php'; ?>
			</nav>
		</header>
		<main class="con-sidebar">
			<section class="titulo">
				<h1><?= e(mb_strtoupper($titulo)) ?></h1>
				<p><?= e($subtitulo ?? '') ?></p>
			</section>
			<?php require __DIR__ . '/mensaje.php'; ?>
