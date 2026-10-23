// Validación rápida en el navegador. El servidor vuelve a validar todo.
document.addEventListener('DOMContentLoaded', () => {
	const formulario = document.getElementById('formIniciarS');
	const mensajeError = document.getElementById('mensajeError');

	formulario.addEventListener('submit', (e) => {
		const correo = document.getElementById('correo').value.trim();
		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
			e.preventDefault();
			mensajeError.textContent = 'Por favor, ingresa un correo electrónico válido.';
		}
	});

	document.getElementById('mostrarPassword').addEventListener('click', () => {
		const password = document.getElementById('password');
		password.type = password.type === 'password' ? 'text' : 'password';
	});
});
