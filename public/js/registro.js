// Validación rápida en el navegador. El servidor vuelve a validar todo.
document.addEventListener('DOMContentLoaded', () => {
	const formulario = document.getElementById('formRegistro');
	const mensajeError = document.getElementById('mensajeError');

	formulario.addEventListener('submit', (e) => {
		const correo = document.getElementById('correo').value.trim();
		const password = document.getElementById('password').value;
		const confirmar = document.getElementById('confirmar').value;
		let error = '';

		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
			error = 'Por favor, ingresa un correo electrónico válido.';
		} else if (password.length < 10 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
			error = 'La contraseña debe tener al menos 10 caracteres, con mayúscula, minúscula y número.';
		} else if (password !== confirmar) {
			error = 'Las contraseñas no coinciden.';
		}

		if (error) {
			e.preventDefault();
			mensajeError.textContent = error;
		}
	});

	document.getElementById('mostrarPassword').addEventListener('click', () => {
		const campo = document.getElementById('password');
		campo.type = campo.type === 'password' ? 'text' : 'password';
	});
});
