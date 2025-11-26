<?php include_once __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $page_title; ?></h1>

    <!-- Formulario de Producto -->
    <form id="productForm" method="POST" action="productos.php<?php echo isset($producto_a_editar) ? '?accion=editar&id=' . $producto_a_editar['id'] : ''; ?>" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="guardar_producto">
        <input type="hidden" name="id" value="<?php echo $producto_a_editar['id'] ?? ''; ?>">
        <input type="hidden" id="imagen_url" name="imagen_url" value="<?php echo htmlspecialchars($producto_a_editar['imagen_url'] ?? ''); ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nombre" class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($producto_a_editar['nombre'] ?? ''); ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label for="precio_venta" class="form-label">Precio de Venta ($)</label>
                <input type="number" step="0.01" class="form-control" name="precio_venta" value="<?php echo htmlspecialchars($producto_a_editar['precio_venta'] ?? ''); ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Imagen del Producto</label>
                <div class="input-group">
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#imageGalleryModal">Seleccionar Imagen</button>
                </div>
                <div class="mt-2">
                    <img id="imagePreview" src="<?php echo !empty($producto_a_editar['imagen_url']) ? htmlspecialchars($producto_a_editar['imagen_url']) : 'public/img/placeholder.png'; ?>" alt="Vista previa" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                </div>
            </div>
        </div>
        
        <hr>
        <h5>Receta</h5>
        <div id="recipe-container">
            <!-- Los ingredientes se cargarán aquí dinámicamente -->
        </div>
        <button type="button" id="add-ingredient-btn" class="btn btn-info btn-sm">Añadir Ingrediente</button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#quickSubproductModal">Crear Subproducto Rápido</button>
        
        <hr>
        <button type="submit" class="btn btn-primary">Guardar Producto</button>
        <a href="productos.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<!-- Modal de Galería de Imágenes -->
<div class="modal fade" id="imageGalleryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar una Imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="galleryContent" class="row g-2">
                    <p>Cargando imágenes...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear Subproducto Rápido -->
<div class="modal fade" id="quickSubproductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Subproducto Rápido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickSubproductForm">
                    <div class="mb-3">
                        <label for="sub_nombre" class="form-label">Nombre</label>
                        <input type="text" id="sub_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="sub_stock" class="form-label">Stock Inicial</label>
                        <input type="number" id="sub_stock" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="sub_unidad" class="form-label">Unidad de Medida</label>
                        <input type="text" id="sub_unidad" class="form-control" placeholder="kg, l, unidad" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="saveQuickSubproduct" class="btn btn-primary">Guardar Subproducto</button>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addIngredientBtn = document.getElementById('add-ingredient-btn');
    const recipeContainer = document.getElementById('recipe-container');
    let ingredientIndex = 0;

    const subproductos = <?php echo json_encode($subproductos); ?>;
    const receta_existente = <?php echo isset($receta_existente) ? json_encode($receta_existente) : '[]'; ?>;

    // Función para añadir una fila de ingrediente
    function addIngredientRow(ingrediente = null) {
        const div = document.createElement('div');
        div.classList.add('row', 'mb-2', 'align-items-center');
        
        const subproductoId = ingrediente ? ingrediente.subproducto_id : '';
        const cantidad = ingrediente ? ingrediente.cantidad : '';

        div.innerHTML = `
            <div class="col-md-5">
                <select name="receta[${ingredientIndex}][id]" class="form-select">
                    <option value="">-- Seleccione --</option>
                    ${subproductos.map(s => `<option value="${s.id}" ${s.id == subproductoId ? 'selected' : ''}>${s.nombre} (${s.unidad_medida})</option>`).join('')}
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="receta[${ingredientIndex}][cantidad]" class="form-control" placeholder="Cantidad" value="${cantidad}" required>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-danger btn-sm remove-ingredient-btn">Quitar</button>
            </div>
        `;
        recipeContainer.appendChild(div);
        ingredientIndex++;
    }

    // Cargar ingredientes existentes si estamos en modo edición
    if (receta_existente.length > 0) {
        receta_existente.forEach(ingrediente => {
            addIngredientRow(ingrediente);
        });
    }

    // Evento para el botón de añadir ingrediente
    addIngredientBtn.addEventListener('click', () => {
        addIngredientRow();
    });

    // Evento para quitar un ingrediente
    recipeContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-ingredient-btn')) {
            e.target.closest('.row').remove();
        }
    });

    // Lógica para el modal de subproducto rápido
    const saveBtn = document.getElementById('saveQuickSubproduct');
    saveBtn.addEventListener('click', async () => {
        const nombre = document.getElementById('sub_nombre').value;
        const stock = document.getElementById('sub_stock').value;
        const unidad = document.getElementById('sub_unidad').value;

        if (!nombre || !stock || !unidad) {
            alert('Todos los campos son obligatorios.');
            return;
        }

        const formData = new FormData();
        formData.append('accion', 'crear_subproducto');
        formData.append('nombre', nombre);
        formData.append('stock', stock);
        formData.append('unidad_medida', unidad);

        try {
            const response = await fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                subproductos.push(result.subproducto);
                // Actualizar todos los selectores de ingredientes
                document.querySelectorAll('#recipe-container select').forEach(select => {
                    const newOption = new Option(`${result.subproducto.nombre} (${result.subproducto.unidad_medida})`, result.subproducto.id);
                    select.add(newOption);
                });
                alert('Subproducto creado con éxito.');
                document.getElementById('quickSubproductForm').reset();
                var modal = bootstrap.Modal.getInstance(document.getElementById('quickSubproductModal'));
                modal.hide();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error en AJAX:', error);
            alert('Hubo un error de comunicación.');
        }
    });

    // --- LÓGICA PARA LA GALERÍA DE IMÁGENES ---
    const imageGalleryModal = document.getElementById('imageGalleryModal');
    const galleryContent = document.getElementById('galleryContent');
    const imagePreview = document.getElementById('imagePreview');
    const imageUrlInput = document.getElementById('imagen_url');

    imageGalleryModal.addEventListener('show.bs.modal', async () => {
        galleryContent.innerHTML = '<p>Cargando imágenes...</p>';
        try {
            const response = await fetch('ajax_handler.php?accion=listar_imagenes');
            const images = await response.json();
            
            galleryContent.innerHTML = ''; // Limpiar contenido
            if (images.length > 0) {
                images.forEach(img_url => {
                    const col = document.createElement('div');
                    col.className = 'col-3';
                    col.innerHTML = `<img src="${img_url}" class="img-fluid img-thumbnail gallery-item" style="cursor: pointer; height: 150px; object-fit: cover;">`;
                    galleryContent.appendChild(col);
                });
            } else {
                galleryContent.innerHTML = '<p>No hay imágenes en la carpeta `public/uploads/`.</p>';
            }
        } catch (error) {
            console.error('Error al cargar la galería:', error);
            galleryContent.innerHTML = '<p>Error al cargar las imágenes.</p>';
        }
    });

    galleryContent.addEventListener('click', function(e) {
        if (e.target.classList.contains('gallery-item')) {
            const selectedImageUrl = e.target.src;
            imageUrlInput.value = selectedImageUrl;
            imagePreview.src = selectedImageUrl;
            
            var modal = bootstrap.Modal.getInstance(imageGalleryModal);
            modal.hide();
        }
    });
});
</script>