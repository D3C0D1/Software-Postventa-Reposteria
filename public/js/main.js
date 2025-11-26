document.addEventListener('DOMContentLoaded', function() {
    // El código del sidebarToggle ha sido eliminado

    // --- Lógica para los modales (sin cambios) ---
    var modalSubproducto = document.getElementById("modalSubproducto");
    var btnCrearSubproducto = document.getElementById("btnCrearSubproducto");
    var spanCloseSubproducto = document.getElementsByClassName("close")[0];

    if(btnCrearSubproducto) {
        btnCrearSubproducto.onclick = function() {
            modalSubproducto.style.display = "block";
        }
    }

    if(spanCloseSubproducto) {
        spanCloseSubproducto.onclick = function() {
            modalSubproducto.style.display = "none";
        }
    }

    var modalCompra = document.getElementById("modalCompra");
    var btnFinalizar = document.getElementById("btnFinalizarCompra");
    var spanCloseCompra = document.getElementsByClassName("close")[1];

    if(btnFinalizar) {
        btnFinalizar.onclick = function() {
            modalCompra.style.display = "block";
        }
    }

    if(spanCloseCompra) {
        spanCloseCompra.onclick = function() {
            modalCompra.style.display = "none";
        }
    }

    window.onclick = function(event) {
        if (event.target == modalSubproducto) {
            modalSubproducto.style.display = "none";
        }
        if (event.target == modalCompra) {
            modalCompra.style.display = "none";
        }
    }

    // SE HA ELIMINADO LA LÓGICA DEL CARRITO DE AQUÍ
});