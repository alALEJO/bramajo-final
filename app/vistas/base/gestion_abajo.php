		</main>
		<?php require __DIR__ . '/pie.php'; ?>
		<script src="/js/confirmar.js"></script>
		<script>
			const navToggle = document.getElementById('navToggle');
			if (navToggle) {
				navToggle.addEventListener('click', function () {
					document.getElementById('headerNav').classList.toggle('abierto');
				});
			}
		</script>
	</body>
</html>
