// Pide confirmación antes de las acciones que no se pueden deshacer.
document.querySelectorAll('.peligro').forEach((boton) => {
	boton.addEventListener('click', (evento) => {
		if (!confirm('¿Seguro que querés hacerlo?')) {
			evento.preventDefault();
		}
	});
});
