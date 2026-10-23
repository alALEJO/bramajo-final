function mostrarventana(boton) {
    document.getElementById("idTorneo").value = boton.dataset.id;
    document.getElementById("torneo").value = boton.dataset.nombre;
    document.getElementById("ventana").classList.add("mostrar");
}

function cerrarVentana() {
    document.getElementById("ventana").classList.remove("mostrar");
}
