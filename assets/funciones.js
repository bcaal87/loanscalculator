window.onload = function() {
    var tablaContainer = document.getElementById('tabla-container');
    if (tablaContainer.style.display === 'none') {
        tablaContainer.style.display = 'block';
    }
}

document.getElementById('ocultar').addEventListener('click', function() {
    var tablaContainer = document.getElementById('tabla-container');
    if (tablaContainer.style.display === 'block') {
        tablaContainer.style.display = 'none';
    }
});

document.getElementById('imprimir').addEventListener('click', function() {
    window.print();
});

document.getElementById('resumen').addEventListener('click', function() {
    // Lógica para generar el resumen de la tabla de pagos
});

// Función para mostrar la tabla de pagos y los botones
function mostrarTablaPagos() {
    var tablaContainer = document.getElementById('tabla-container');
    var botonesContainer = document.getElementById('botones-container');
    if (tablaContainer.style.display === 'none') {
        tablaContainer.style.display = 'block';
    }
    if (botonesContainer.style.display === 'flex') {
        botonesContainer.style.display = 'flex'; // Mostrar los botones cuando se muestra la tabla
    }
}


// Event listener para el botón "Ver tabla de pagos"
document.getElementById('ver-tabla-pagos').addEventListener('click', function() {
    mostrarTablaPagos();
});


