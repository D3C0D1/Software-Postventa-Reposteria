<?php
require_once __DIR__ . '/../config/database.php';

$db = conectarDB();

// Consultar los productos que están activos
$query_productos = "SELECT id, nombre, imagen_url, precio_venta, salsas_obligatorias, adiciones_obligatorias, 'producto' as tipo FROM productos WHERE estado = 'activo' ORDER BY nombre ASC";
$resultado_productos = $db->query($query_productos);

// Consultar los subproductos que están activos por categoría
$query_subproductos = "SELECT id, nombre, imagen, precio, categoria, 'subproducto' as tipo FROM subproductos WHERE stock > 0 ORDER BY categoria, nombre ASC";
$resultado_subproductos = $db->query($query_subproductos);

?>

<div class="container-fluid my-5">
    <!-- Barra de Búsqueda -->
    <div class="row mb-4 justify-content-center">
        <div class="col-md-6">
            <div class="input-group search-bar-container">
                <span class="input-group-text search-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="buscadorItems" class="form-control" placeholder="Buscar productos y adiciones...">
            </div>
        </div>
    </div>

    <!-- Sección de Productos -->
    <h2 class="text-center mb-4">Nuestros Productos</h2>
    <div id="galeriaProductos" class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-5 g-4">
        <?php if ($resultado_productos && $resultado_productos->num_rows > 0): ?>
            <?php while ($producto = $resultado_productos->fetch_assoc()): ?>
                <div class="col producto-item">
                    <div class="card h-100 text-center product-card">
                        <img src="<?php echo htmlspecialchars($producto['imagen_url']); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title nombre-producto"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                            <p class="card-text fs-5 fw-bold text-success mt-auto">$<?php echo number_format($producto['precio_venta'], 0, ',', '.'); ?></p>
                            
                            <?php if ($producto['salsas_obligatorias'] > 0 || $producto['adiciones_obligatorias'] > 0): ?>
                                <!-- Producto con personalización -->
                                <div class="mt-2">
                                    <div class="input-group mb-2">
                                        <input type="number" class="form-control cantidad-producto" value="1" min="1" data-producto-id="<?php echo $producto['id']; ?>" aria-label="Cantidad">
                                    </div>
                                    <button class="btn btn-warning w-100" type="button" onclick="abrirModalPersonalizacion(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>', <?php echo $producto['salsas_obligatorias']; ?>, <?php echo $producto['adiciones_obligatorias']; ?>)">
                                        <i class="fas fa-cogs"></i> Agregar
                                    </button>
                                </div>
                            <?php else: ?>
                                <!-- Producto sin personalización -->
                                <form action="cart_handler.php" method="POST" class="mt-2">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                    <input type="hidden" name="producto_tipo" value="producto">
                                    <div class="input-group">
                                        <input type="number" name="cantidad" class="form-control" value="1" min="1" aria-label="Cantidad">
                                        <button class="btn btn-primary" type="submit">Agregar</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center">No hay productos disponibles en este momento.</p>
            </div>
        <?php endif; ?>
    </div>

    <hr class="my-5">

    <!-- Sección de Subproductos -->
    
    <div id="galeriaSubproductos" class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-5 g-4">
        <?php if ($resultado_subproductos && $resultado_subproductos->num_rows > 0): ?>
            <?php while ($subproducto = $resultado_subproductos->fetch_assoc()): ?>
                <div class="col producto-item">
                    <div class="card h-100 text-center product-card">
                        <img src="public/uploads/subproductos/<?php echo htmlspecialchars($subproducto['imagen']); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($subproducto['nombre']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title nombre-producto"><?php echo htmlspecialchars($subproducto['nombre']); ?></h5>
                            <p class="card-text fs-5 fw-bold text-success mt-auto">$<?php echo number_format($subproducto['precio'], 0, ',', '.'); ?></p>
                            <form action="cart_handler.php" method="POST" class="mt-2">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="producto_id" value="<?php echo $subproducto['id']; ?>">
                                <input type="hidden" name="producto_tipo" value="subproducto">
                                <div class="input-group">
                                    <input type="number" name="cantidad" class="form-control" value="1" min="1" aria-label="Cantidad">
                                    <button class="btn btn-primary" type="submit">Agregar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center">No hay adiciones disponibles en este momento.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Personalización -->
<div class="modal fade" id="modalPersonalizacion" tabindex="-1" aria-labelledby="modalPersonalizacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPersonalizacionLabel">Personalizar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoPersonalizacion">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAgregarPersonalizado" onclick="agregarProductoPersonalizado()">Agregar al Carrito</button>
            </div>
        </div>
    </div>
</div>

<?php
if($db) $db->close();
?>

<script>
let productoActual = null;
let salsasSeleccionadas = {}; // Cambiar a objeto para contar cantidades
let adicionesSeleccionadas = {}; // Objeto para contar cantidades

document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscadorItems');
    const items = document.querySelectorAll('.producto-item');

    buscador.addEventListener('keyup', function() {
        const textoBuscado = buscador.value.toLowerCase();

        items.forEach(function(item) {
            const nombreProducto = item.querySelector('.nombre-producto').textContent.toLowerCase();
            if (nombreProducto.includes(textoBuscado)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

function abrirModalPersonalizacion(productoId, nombreProducto, salsasObligatorias, adicionesObligatorias) {
    productoActual = {
        id: productoId,
        nombre: nombreProducto,
        salsasObligatorias: salsasObligatorias,
        adicionesObligatorias: adicionesObligatorias,
        cantidad: document.querySelector(`input[data-producto-id="${productoId}"]`).value
    };
    
    salsasSeleccionadas = {}; // Reiniciar objeto
    adicionesSeleccionadas = {}; // Reiniciar objeto
    
    document.getElementById('modalPersonalizacionLabel').textContent = `Personalizar: ${nombreProducto}`;
    
    // Cargar contenido del modal
    cargarOpcionesPersonalizacion();
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalPersonalizacion'));
    modal.show();
}

function cargarOpcionesPersonalizacion() {
    const contenido = document.getElementById('contenidoPersonalizacion');
    contenido.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando opciones...</div>';
    
    fetch('ajax/get_customization_options.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            producto_id: productoActual.id,
            salsas_obligatorias: productoActual.salsasObligatorias,
            adiciones_obligatorias: productoActual.adicionesObligatorias
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarOpcionesPersonalizacion(data.salsas, data.adiciones);
        } else {
            contenido.innerHTML = '<div class="alert alert-danger">Error al cargar las opciones de personalización.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        contenido.innerHTML = '<div class="alert alert-danger">Error de conexión.</div>';
    });
}

function mostrarOpcionesPersonalizacion(salsas, adiciones) {
    let html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Cantidad:</h6>
                <input type="number" id="cantidadPersonalizada" class="form-control mb-3" value="${productoActual.cantidad}" min="1" onchange="actualizarCantidadPersonalizada(this.value)">
            </div>
        </div>
    `;
    
    // Salsas obligatorias (nueva lógica con contadores)
    if (productoActual.salsasObligatorias > 0) {
        const totalSalsasObligatorias = productoActual.salsasObligatorias * productoActual.cantidad;
        html += `
            <div class="mb-4">
                <h6>Salsas Obligatorias (Selecciona ${totalSalsasObligatorias} en total):</h6>
                <div class="mb-2">
                    <input type="text" id="buscadorSalsas" class="form-control" placeholder="Buscar salsas...">
                </div>
                <div class="mb-2">
                    <small class="text-muted">Puedes seleccionar la misma salsa múltiples veces</small>
                </div>
                <div id="listaSalsas" class="row">
        `;
        
        salsas.forEach(salsa => {
            html += `
                <div class="col-md-12 mb-3 salsa-item">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-0">${salsa.nombre}</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidadSalsa(${salsa.id}, '${salsa.nombre}', -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control text-center" id="cantidad_salsa_${salsa.id}" value="0" min="0" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidadSalsa(${salsa.id}, '${salsa.nombre}', 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
                <div class="mb-2">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" id="progresoSalsas" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="${totalSalsasObligatorias}">0/${totalSalsasObligatorias}</div>
                    </div>
                </div>
                <div id="errorSalsas" class="text-danger mt-2" style="display: none;"></div>
            </div>
        `;
    }
    
    // Adiciones obligatorias (lógica con contadores)
    if (productoActual.adicionesObligatorias > 0) {
        const totalAdicionesObligatorias = productoActual.adicionesObligatorias * productoActual.cantidad;
        html += `
            <div class="mb-4">
                <h6>Adiciones Obligatorias (Selecciona ${totalAdicionesObligatorias} en total):</h6>
                <div class="mb-2">
                    <input type="text" id="buscadorAdiciones" class="form-control" placeholder="Buscar adiciones...">
                </div>
                <div class="mb-2">
                    <small class="text-muted">Puedes seleccionar la misma adición múltiples veces</small>
                </div>
                <div id="listaAdiciones" class="row">
        `;
        
        adiciones.forEach(adicion => {
            html += `
                <div class="col-md-12 mb-3 adicion-item">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-0">${adicion.nombre}</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidadAdicion(${adicion.id}, '${adicion.nombre}', -1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control text-center" id="cantidad_adicion_${adicion.id}" value="0" min="0" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidadAdicion(${adicion.id}, '${adicion.nombre}', 1)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
                <div class="mb-2">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" id="progresoAdiciones" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="${productoActual.adicionesObligatorias}">0/${productoActual.adicionesObligatorias}</div>
                    </div>
                </div>
                <div id="errorAdiciones" class="text-danger mt-2" style="display: none;"></div>
            </div>
        `;
    }
    
    document.getElementById('contenidoPersonalizacion').innerHTML = html;
    
    // Agregar eventos de búsqueda
    if (document.getElementById('buscadorSalsas')) {
        document.getElementById('buscadorSalsas').addEventListener('keyup', function() {
            filtrarOpciones('salsa-item', this.value);
        });
    }
    
    if (document.getElementById('buscadorAdiciones')) {
        document.getElementById('buscadorAdiciones').addEventListener('keyup', function() {
            filtrarOpciones('adicion-item', this.value);
        });
    }
}

function filtrarOpciones(className, texto) {
    const items = document.querySelectorAll(`.${className}`);
    const textoBuscado = texto.toLowerCase();
    
    items.forEach(item => {
        const label = item.querySelector('h6').textContent.toLowerCase();
        if (label.includes(textoBuscado)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function actualizarCantidadPersonalizada(nuevaCantidad) {
    nuevaCantidad = parseInt(nuevaCantidad) || 1;
    if (nuevaCantidad < 1) nuevaCantidad = 1;
    
    productoActual.cantidad = nuevaCantidad;
    
    // Actualizar las barras de progreso y textos
    if (productoActual.salsasObligatorias > 0) {
        const totalSalsasObligatorias = productoActual.salsasObligatorias * productoActual.cantidad;
        const progreso = document.getElementById('progresoSalsas');
        progreso.setAttribute('aria-valuemax', totalSalsasObligatorias);
        document.querySelector('h6:contains("Salsas Obligatorias")').textContent = `Salsas Obligatorias (Selecciona ${totalSalsasObligatorias} en total):`;
        actualizarProgresoSalsas();
    }
    
    if (productoActual.adicionesObligatorias > 0) {
        const totalAdicionesObligatorias = productoActual.adicionesObligatorias * productoActual.cantidad;
        const progreso = document.getElementById('progresoAdiciones');
        progreso.setAttribute('aria-valuemax', totalAdicionesObligatorias);
        document.querySelector('h6:contains("Adiciones Obligatorias")').textContent = `Adiciones Obligatorias (Selecciona ${totalAdicionesObligatorias} en total):`;
        actualizarProgresoAdiciones();
    }
}

function cambiarCantidadSalsa(id, nombre, cambio) {
    const input = document.getElementById(`cantidad_salsa_${id}`);
    const cantidadActual = parseInt(input.value) || 0;
    const nuevaCantidad = Math.max(0, cantidadActual + cambio);
    
    // Calcular total actual de salsas
    const totalActual = Object.values(salsasSeleccionadas).reduce((sum, item) => sum + item.cantidad, 0);
    const totalSalsasObligatorias = productoActual.salsasObligatorias * productoActual.cantidad;
    
    // Verificar si se puede agregar más
    if (cambio > 0 && totalActual >= totalSalsasObligatorias) {
        const errorDiv = document.getElementById('errorSalsas');
        errorDiv.textContent = `Solo puedes seleccionar ${totalSalsasObligatorias} salsa(s) en total.`;
        errorDiv.style.display = 'block';
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 3000);
        return;
    }
    
    // Actualizar cantidad
    input.value = nuevaCantidad;
    
    if (nuevaCantidad > 0) {
        salsasSeleccionadas[id] = {
            id: id,
            nombre: nombre,
            cantidad: nuevaCantidad
        };
    } else {
        delete salsasSeleccionadas[id];
    }
    
    // Actualizar barra de progreso
    actualizarProgresoSalsas();
}

function cambiarCantidadAdicion(id, nombre, cambio) {
    const input = document.getElementById(`cantidad_adicion_${id}`);
    const cantidadActual = parseInt(input.value) || 0;
    const nuevaCantidad = Math.max(0, cantidadActual + cambio);
    
    // Calcular total actual de adiciones
    const totalActual = Object.values(adicionesSeleccionadas).reduce((sum, item) => sum + item.cantidad, 0);
    const totalAdicionesObligatorias = productoActual.adicionesObligatorias * productoActual.cantidad;
    
    // Verificar si se puede agregar más
    if (cambio > 0 && totalActual >= totalAdicionesObligatorias) {
        const errorDiv = document.getElementById('errorAdiciones');
        errorDiv.textContent = `Solo puedes seleccionar ${totalAdicionesObligatorias} adición(es) en total.`;
        errorDiv.style.display = 'block';
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 3000);
        return;
    }
    
    // Actualizar cantidad
    input.value = nuevaCantidad;
    
    if (nuevaCantidad > 0) {
        adicionesSeleccionadas[id] = {
            id: id,
            nombre: nombre,
            cantidad: nuevaCantidad
        };
    } else {
        delete adicionesSeleccionadas[id];
    }
    
    // Actualizar barra de progreso
    actualizarProgresoAdiciones();
}

function actualizarProgresoSalsas() {
    const totalSeleccionado = Object.values(salsasSeleccionadas).reduce((sum, item) => sum + item.cantidad, 0);
    const totalSalsasObligatorias = productoActual.salsasObligatorias * productoActual.cantidad;
    const progreso = document.getElementById('progresoSalsas');
    const porcentaje = Math.min(100, (totalSeleccionado / totalSalsasObligatorias) * 100);
    
    progreso.style.width = porcentaje + '%';
    progreso.textContent = `${totalSeleccionado}/${totalSalsasObligatorias}`;
    progreso.setAttribute('aria-valuenow', totalSeleccionado);
    
    // Cambiar color según el progreso
    progreso.className = 'progress-bar';
    if (totalSeleccionado === totalSalsasObligatorias) {
        progreso.classList.add('bg-success');
    } else if (totalSeleccionado > totalSalsasObligatorias) {
        progreso.classList.add('bg-danger');
    } else {
        progreso.classList.add('bg-primary');
    }
}

function actualizarProgresoAdiciones() {
    const totalSeleccionado = Object.values(adicionesSeleccionadas).reduce((sum, item) => sum + item.cantidad, 0);
    const totalAdicionesObligatorias = productoActual.adicionesObligatorias * productoActual.cantidad;
    const progreso = document.getElementById('progresoAdiciones');
    const porcentaje = Math.min(100, (totalSeleccionado / totalAdicionesObligatorias) * 100);
    
    progreso.style.width = porcentaje + '%';
    progreso.textContent = `${totalSeleccionado}/${totalAdicionesObligatorias}`;
    progreso.setAttribute('aria-valuenow', totalSeleccionado);
    
    // Cambiar color según el progreso
    progreso.className = 'progress-bar';
    if (totalSeleccionado === totalAdicionesObligatorias) {
        progreso.classList.add('bg-success');
    } else if (totalSeleccionado > totalAdicionesObligatorias) {
        progreso.classList.add('bg-danger');
    } else {
        progreso.classList.add('bg-primary');
    }
}

function agregarProductoPersonalizado() {
    // Validar selecciones de salsas
    const totalSalsasObligatorias = productoActual.salsasObligatorias * productoActual.cantidad;
    const totalSalsas = Object.values(salsasSeleccionadas).reduce((sum, item) => sum + item.cantidad, 0);
    if (totalSalsas !== totalSalsasObligatorias) {
        alert(`Debes seleccionar exactamente ${totalSalsasObligatorias} salsa(s) en total.`);
        return;
    }
    
    // Validar selecciones de adiciones
    const totalAdicionesObligatorias = productoActual.adicionesObligatorias * productoActual.cantidad;
    const totalAdiciones = Object.values(adicionesSeleccionadas).reduce((sum, item) => sum + item.cantidad, 0);
    if (totalAdiciones !== totalAdicionesObligatorias) {
        alert(`Debes seleccionar exactamente ${totalAdicionesObligatorias} adición(es) en total.`);
        return;
    }
    
    const cantidad = document.getElementById('cantidadPersonalizada').value;
    
    // Convertir salsas a array para envío
    const salsasArray = [];
    Object.values(salsasSeleccionadas).forEach(item => {
        for (let i = 0; i < item.cantidad; i++) {
            salsasArray.push({id: item.id, nombre: item.nombre});
        }
    });
    
    // Convertir adiciones a array para envío
    const adicionesArray = [];
    Object.values(adicionesSeleccionadas).forEach(item => {
        for (let i = 0; i < item.cantidad; i++) {
            adicionesArray.push({id: item.id, nombre: item.nombre});
        }
    });
    
    // Enviar al carrito
    const formData = new FormData();
    formData.append('action', 'add_customized');
    formData.append('producto_id', productoActual.id);
    formData.append('cantidad', cantidad);
    formData.append('salsas', JSON.stringify(salsasArray));
    formData.append('adiciones', JSON.stringify(adicionesArray));
    
    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalPersonalizacion'));
        modal.hide();
        
        // Recargar página para actualizar carrito
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar el producto al carrito.');
    });
}
</script>
<script>
// Interceptar los formularios de productos normales
document.addEventListener('DOMContentLoaded', function() {
    const productForms = document.querySelectorAll('form[action="cart_handler.php"]');
    
    productForms.forEach(form => {
        if (form.querySelector('input[name="producto_tipo"]').value === 'producto') {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const productoId = form.querySelector('input[name="producto_id"]').value;
                const cantidad = form.querySelector('input[name="cantidad"]').value;
                
                checkIngredientStock(productoId, cantidad, form);
            });
        }
    });
});

// Función para verificar el stock de ingredientes
function checkIngredientStock(productoId, cantidad, form) {
    const formData = new FormData();
    formData.append('producto_id', productoId);
    formData.append('cantidad', cantidad);
    
    fetch('ajax/check_ingredients_stock.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (!data.has_stock) {
                // Mostrar modal de confirmación
                showStockConfirmationModal(data.ingredients, form);
            } else {
                // Si hay stock suficiente, enviar el formulario normalmente
                form.submit();
            }
        } else {
            alert('Error al verificar el stock: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al verificar el stock de ingredientes.');
    });
}

// Función para mostrar el modal de confirmación
function showStockConfirmationModal(ingredients, form) {
    // Crear el modal si no existe
    let modalElement = document.getElementById('stockConfirmationModal');
    if (!modalElement) {
        const modalHTML = `
        <div class="modal fade" id="stockConfirmationModal" tabindex="-1" aria-labelledby="stockConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="stockConfirmationModalLabel"><i class="fas fa-exclamation-triangle"></i> Advertencia de Stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>No hay suficiente stock de los siguientes ingredientes:</p>
                        <ul id="ingredientsList" class="list-group mb-3"></ul>
                        <p class="fw-bold">¿Desea continuar de todas formas? El stock quedará en negativo.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmAddToCart">Sí, continuar</button>
                    </div>
                </div>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        modalElement = document.getElementById('stockConfirmationModal');
    }
    
    // Llenar la lista de ingredientes
    const ingredientsList = document.getElementById('ingredientsList');
    ingredientsList.innerHTML = '';
    ingredients.forEach(ingredient => {
        ingredientsList.innerHTML += `
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>${ingredient.nombre}</span>
            <span>
                <span class="badge bg-danger">Stock: ${ingredient.stock_actual} ${ingredient.unidad_medida}</span>
                <span class="badge bg-primary">Necesario: ${ingredient.cantidad_necesaria} ${ingredient.unidad_medida}</span>
            </span>
        </li>
        `;
    });
    
    // Configurar el botón de confirmación
    const confirmButton = document.getElementById('confirmAddToCart');
    confirmButton.onclick = function() {
        // Añadir el campo force_add al formulario
        const forceAddInput = document.createElement('input');
        forceAddInput.type = 'hidden';
        forceAddInput.name = 'force_add';
        forceAddInput.value = 'true';
        form.appendChild(forceAddInput);
        
        // Enviar el formulario
        form.submit();
        
        // Cerrar el modal
        const modal = bootstrap.Modal.getInstance(modalElement);
        modal.hide();
    };
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Modificar la función de personalización para verificar stock
const originalAgregarProductoPersonalizado = window.agregarProductoPersonalizado;
window.agregarProductoPersonalizado = function() {
    // Validar selecciones de salsas
    const totalSalsasObligatorias = productoActual.salsasObligatorias * productoActual.cantidad;
    const totalSalsas = Object.values(salsasSeleccionadas).reduce((sum, item) => sum + item.cantidad, 0);
    if (totalSalsas !== totalSalsasObligatorias) {
        alert(`Debes seleccionar exactamente ${totalSalsasObligatorias} salsa(s) en total.`);
        return;
    }
    
    // Validar selecciones de adiciones
    const totalAdicionesObligatorias = productoActual.adicionesObligatorias * productoActual.cantidad;
    const totalAdiciones = Object.values(adicionesSeleccionadas).reduce((sum, item) => sum + item.cantidad, 0);
    if (totalAdiciones !== totalAdicionesObligatorias) {
        alert(`Debes seleccionar exactamente ${totalAdicionesObligatorias} adición(es) en total.`);
        return;
    }
    
    const cantidad = document.getElementById('cantidadPersonalizada').value;
    
    // Verificar stock de ingredientes
    const formData = new FormData();
    formData.append('producto_id', productoActual.id);
    formData.append('cantidad', cantidad);
    
    fetch('ajax/check_ingredients_stock.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (!data.has_stock) {
                // Crear un formulario temporal para la confirmación
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = 'cart_handler.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'add_customized';
                tempForm.appendChild(actionInput);
                
                const productoIdInput = document.createElement('input');
                productoIdInput.type = 'hidden';
                productoIdInput.name = 'producto_id';
                productoIdInput.value = productoActual.id;
                tempForm.appendChild(productoIdInput);
                
                const cantidadInput = document.createElement('input');
                cantidadInput.type = 'hidden';
                cantidadInput.name = 'cantidad';
                cantidadInput.value = cantidad;
                tempForm.appendChild(cantidadInput);
                
                // Convertir salsas a array para envío
                const salsasArray = [];
                Object.values(salsasSeleccionadas).forEach(item => {
                    for (let i = 0; i < item.cantidad; i++) {
                        salsasArray.push({id: item.id, nombre: item.nombre});
                    }
                });
                
                const salsasInput = document.createElement('input');
                salsasInput.type = 'hidden';
                salsasInput.name = 'salsas';
                salsasInput.value = JSON.stringify(salsasArray);
                tempForm.appendChild(salsasInput);
                
                // Convertir adiciones a array para envío
                const adicionesArray = [];
                Object.values(adicionesSeleccionadas).forEach(item => {
                    for (let i = 0; i < item.cantidad; i++) {
                        adicionesArray.push({id: item.id, nombre: item.nombre});
                    }
                });
                
                const adicionesInput = document.createElement('input');
                adicionesInput.type = 'hidden';
                adicionesInput.name = 'adiciones';
                adicionesInput.value = JSON.stringify(adicionesArray);
                tempForm.appendChild(adicionesInput);
                
                // Mostrar modal de confirmación
                showStockConfirmationModal(data.ingredients, tempForm);
            } else {
                // Si hay stock suficiente, continuar con la función original
                originalAgregarProductoPersonalizado();
            }
        } else {
            alert('Error al verificar el stock: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al verificar el stock de ingredientes.');
    });
};
</script>
</script>
<style>
.search-bar-container {
    border-radius: 30px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-bar-container .form-control {
    border-left: none;
    border-radius: 0 30px 30px 0;
    height: 48px;
}

.search-bar-container .form-control:focus {
    box-shadow: none;
    border-color: #ced4da;
}

.search-icon {
    background-color: white;
    border-right: none;
    border-radius: 30px 0 0 30px;
    color: #6c757d;
}

.product-card {
    border: 1px solid #e0e0e0;
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%; /* Ocupa la altura del contenedor de la columna */
    display: flex;
    flex-direction: column;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.product-image {
    width: 100%;
    height: 150px; /* Altura ajustada para la imagen */
    object-fit: contain; /* La imagen se ajusta sin deformarse */
    padding: 10px;
}
.card-body {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
</style>