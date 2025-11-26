<?php
$is_dashboard_page = true;
$page_title = '';
require_once 'includes/auth_check.php';
require_once 'config/database.php';

// Proteger esta página solo para administradores
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$db = conectarDB();
$errores = [];
$mensaje = '';

// --- LÓGICA DE PRODUCTOS Y RECETAS ---

// 1. PROCESAR ACCIONES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // --- ACCIÓN: CREAR O ACTUALIZAR PRODUCTO ---
    if ($accion === 'guardar_producto') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $nombre = filter_var(trim($_POST['nombre']), FILTER_SANITIZE_STRING);
        $precio_venta = filter_var($_POST['precio_venta'], FILTER_VALIDATE_FLOAT);
        $estado = filter_var($_POST['estado'], FILTER_SANITIZE_STRING);
        $salsas_obligatorias = filter_var($_POST['salsas_obligatorias'] ?? 0, FILTER_VALIDATE_INT);
        $adiciones_obligatorias = filter_var($_POST['adiciones_obligatorias'] ?? 0, FILTER_VALIDATE_INT);

        // Validación
        if (!$nombre) $errores[] = 'El nombre del producto es obligatorio.';
        if ($precio_venta === false || $precio_venta <= 0) $errores[] = 'El precio debe ser un número positivo.';
        if (!in_array($estado, ['activo', 'inactivo'])) $errores[] = 'El estado no es válido.';
        if ($salsas_obligatorias === false || $salsas_obligatorias < 0) $salsas_obligatorias = 0;
        if ($adiciones_obligatorias === false || $adiciones_obligatorias < 0) $adiciones_obligatorias = 0;

        // Lógica de subida de imagen (opcional)
        $imagen_url = $_POST['imagen_actual'] ?? ''; // Mantener la imagen si no se sube una nueva
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $directorio_imagenes = 'public/uploads/';
            if (!is_dir($directorio_imagenes)) {
                mkdir($directorio_imagenes, 0755, true);
            }
            $nombre_imagen = md5(uniqid(rand(), true)) . ".jpg";
            $ruta_imagen = $directorio_imagenes . $nombre_imagen;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_imagen)) {
                // Si había una imagen anterior, eliminarla (opcional)
                if ($imagen_url && file_exists($imagen_url)) {
                    unlink($imagen_url);
                }
                $imagen_url = $ruta_imagen;
            } else {
                $errores[] = 'Error al subir la imagen.';
            }
        }

        if (empty($errores)) {
            if ($id) { // Actualizar
                $stmt = $db->prepare("UPDATE productos SET nombre = ?, precio_venta = ?, imagen_url = ?, estado = ?, salsas_obligatorias = ?, adiciones_obligatorias = ? WHERE id = ?");
                $stmt->bind_param('sdssiis', $nombre, $precio_venta, $imagen_url, $estado, $salsas_obligatorias, $adiciones_obligatorias, $id);
                if ($stmt->execute()) {
                    $mensaje = 'Producto actualizado correctamente.';
                } else {
                    $errores[] = 'Error al actualizar el producto.';
                }
            } else { // Crear
                $stmt = $db->prepare("INSERT INTO productos (nombre, precio_venta, imagen_url, estado, salsas_obligatorias, adiciones_obligatorias) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sdssii', $nombre, $precio_venta, $imagen_url, $estado, $salsas_obligatorias, $adiciones_obligatorias);
                if ($stmt->execute()) {
                    $mensaje = 'Producto creado correctamente.';
                } else {
                    $errores[] = 'Error al crear el producto.';
                }
            }
            $stmt->close();
        }
    }

    // --- ACCIÓN: AÑADIR INGREDIENTE A RECETA ---
    if ($accion === 'agregar_ingrediente') {
        $producto_id = filter_var($_POST['producto_id'], FILTER_VALIDATE_INT);
        $subproducto_id = filter_var($_POST['subproducto_id'], FILTER_VALIDATE_INT);
        $cantidad = filter_var($_POST['cantidad_necesaria'], FILTER_VALIDATE_FLOAT);

        if ($producto_id && $subproducto_id && $cantidad > 0) {
            $stmt = $db->prepare("INSERT INTO recetas (producto_id, subproducto_id, cantidad_necesaria) VALUES (?, ?, ?)");
            $stmt->bind_param('iid', $producto_id, $subproducto_id, $cantidad);
            if ($stmt->execute()) {
                $mensaje = 'Ingrediente añadido a la receta.';
            } else {
                $errores[] = 'Error al añadir el ingrediente. Es posible que ya exista.';
            }
            $stmt->close();
        } else {
            $errores[] = 'Datos inválidos para añadir ingrediente.';
        }
    }
}

// 2. PROCESAR ACCIONES (GET)
$accion_get = $_GET['accion'] ?? '';
$id_get = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

// --- ACCIÓN: ELIMINAR PRODUCTO ---
if ($accion_get === 'eliminar' && $id_get) {
    $db->begin_transaction(); // Iniciar transacción

    try {
        // Opcional: eliminar imagen asociada
        $stmt_img = $db->prepare("SELECT imagen_url FROM productos WHERE id = ?");
        $stmt_img->bind_param('i', $id_get);
        $stmt_img->execute();
        $res_img = $stmt_img->get_result()->fetch_assoc();
        if ($res_img && !empty($res_img['imagen_url']) && file_exists($res_img['imagen_url'])) {
            unlink($res_img['imagen_url']);
        }
        $stmt_img->close();

        // 1. Eliminar de venta_detalles (ya que no tiene ON DELETE CASCADE)
        $stmt_ventas = $db->prepare("DELETE FROM venta_detalles WHERE producto_id = ?");
        $stmt_ventas->bind_param('i', $id_get);
        $stmt_ventas->execute();
        $stmt_ventas->close();

        // 2. Eliminar de recetas (tiene ON DELETE CASCADE, pero es bueno ser explícito)
        $stmt_recetas = $db->prepare("DELETE FROM recetas WHERE producto_id = ?");
        $stmt_recetas->bind_param('i', $id_get);
        $stmt_recetas->execute();
        $stmt_recetas->close();

        // 3. Ahora eliminar el producto
        $stmt_prod = $db->prepare("DELETE FROM productos WHERE id = ?");
        $stmt_prod->bind_param('i', $id_get);
        $stmt_prod->execute();
        $stmt_prod->close();

        $db->commit(); // Confirmar cambios
        $mensaje = 'Producto y sus datos asociados eliminados correctamente.';

    } catch (mysqli_sql_exception $e) {
        $db->rollback(); // Revertir cambios en caso de error
        $errores[] = 'Error al eliminar el producto: ' . $e->getMessage();
    }
}

// --- ACCIÓN: ELIMINAR INGREDIENTE DE RECETA ---
if ($accion_get === 'eliminar_ingrediente' && $id_get) {
    $stmt = $db->prepare("DELETE FROM recetas WHERE id = ?");
    $stmt->bind_param('i', $id_get);
    if ($stmt->execute()) {
        $mensaje = 'Ingrediente eliminado de la receta.';
    } else {
        $errores[] = 'Error al eliminar el ingrediente.';
    }
    $stmt->close();
}

// --- OBTENER DATOS PARA LA VISTA ---

// Obtener lista de todos los productos
$productos = $db->query("SELECT * FROM productos ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

// Obtener lista de todos los subproductos (para el dropdown)
$subproductos = $db->query("SELECT id, nombre, unidad_medida FROM subproductos ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

// Si estamos en modo edición, obtener los datos del producto y su receta
$producto_a_editar = null;
$receta_del_producto = [];
if ($accion_get === 'editar' && $id_get) {
    $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param('i', $id_get);
    $stmt->execute();
    $producto_a_editar = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($producto_a_editar) {
        $stmt_receta = $db->prepare("SELECT r.id, s.nombre, r.cantidad_necesaria, s.unidad_medida FROM recetas r JOIN subproductos s ON r.subproducto_id = s.id WHERE r.producto_id = ?");
        $stmt_receta->bind_param('i', $id_get);
        $stmt_receta->execute();
        $receta_del_producto = $stmt_receta->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_receta->close();
    }
}

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Productos</h1>
    <p class="lead">Crea, edita y gestiona los productos finales y sus recetas.</p>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    <?php foreach ($errores as $error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endforeach; ?>

    <!-- Formulario de Producto y Receta -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-cookie-bite"></i>
            <?php echo $producto_a_editar ? 'Editando Producto: ' . htmlspecialchars($producto_a_editar['nombre']) : 'Crear Nuevo Producto'; ?>
        </div>
        <div class="card-body">
            <!-- Formulario Principal del Producto -->
            <form action="productos.php<?php echo $producto_a_editar ? '?accion=editar&id=' . $producto_a_editar['id'] : ''; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="guardar_producto">
                <input type="hidden" name="id" value="<?php echo $producto_a_editar['id'] ?? ''; ?>">
                <input type="hidden" name="imagen_actual" value="<?php echo $producto_a_editar['imagen_url'] ?? ''; ?>">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nombre" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($producto_a_editar['nombre'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="precio_venta" class="form-label">Precio ($)</label>
                        <input type="number" step="0.01" class="form-control" name="precio_venta" value="<?php echo htmlspecialchars($producto_a_editar['precio_venta'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" class="form-control">
                            <option value="activo" <?php echo (isset($producto_a_editar['estado']) && $producto_a_editar['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo (isset($producto_a_editar['estado']) && $producto_a_editar['estado'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="salsas_obligatorias" class="form-label">Salsas Obligatorias</label>
                        <input type="number" class="form-control" name="salsas_obligatorias" value="<?php echo htmlspecialchars($producto_a_editar['salsas_obligatorias'] ?? 0); ?>" min="0">
                        <small class="form-text text-muted">Número de salsas gratuitas que debe elegir el cliente</small>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="adiciones_obligatorias" class="form-label">Adiciones Obligatorias</label>
                        <input type="number" class="form-control" name="adiciones_obligatorias" value="<?php echo htmlspecialchars($producto_a_editar['adiciones_obligatorias'] ?? 0); ?>" min="0">
                        <small class="form-text text-muted">Número de adiciones gratuitas que debe elegir el cliente</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="imagen" class="form-label">Imagen</label>
                        <input type="file" class="form-control" name="imagen" accept="image/jpeg, image/png">
                        <?php if (!empty($producto_a_editar['imagen_url'])): ?>
                            <small class="form-text text-muted">Imagen actual: <a href="<?php echo htmlspecialchars($producto_a_editar['imagen_url']); ?>" target="_blank">ver imagen</a>. Dejar en blanco para no cambiar.</small>
                        <?php endif; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $producto_a_editar ? 'Actualizar Producto' : 'Crear Producto'; ?></button>
                <?php if ($producto_a_editar): ?>
                    <a href="productos.php" class="btn btn-secondary">Cancelar Edición</a>
                <?php endif; ?>
            </form>

            <!-- Sección de Receta (solo si se está editando) -->
            <?php if ($producto_a_editar): ?>
                <hr class="my-4">
                <h5><i class="fas fa-book-open"></i> Receta para "<?php echo htmlspecialchars($producto_a_editar['nombre']); ?>"</h5>
                
                <!-- Formulario para añadir ingredientes -->
                <form action="productos.php?accion=editar&id=<?php echo $producto_a_editar['id']; ?>" method="POST" class="mt-3 p-3 bg-light rounded">
                    <input type="hidden" name="accion" value="agregar_ingrediente">
                    <input type="hidden" name="producto_id" value="<?php echo $producto_a_editar['id']; ?>">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-2">
                            <label for="subproducto_id" class="form-label">Ingrediente</label>
                            <select name="subproducto_id" class="form-select" required>
                                <option value="">-- Seleccione un ingrediente --</option>
                                <?php foreach ($subproductos as $sub): ?>
                                    <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['nombre']) . ' (' . htmlspecialchars($sub['unidad_medida']) . ')'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="cantidad_necesaria" class="form-label">Cantidad Necesaria</label>
                            <input type="number" step="0.01" name="cantidad_necesaria" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="submit" class="btn btn-success w-100">Añadir a Receta</button>
                        </div>
                    </div>
                </form>

                <!-- Tabla de ingredientes actuales -->
                <table class="table table-sm table-striped mt-4">
                    <thead><tr><th>Ingrediente</th><th>Cantidad</th><th>Acción</th></tr></thead>
                    <tbody>
                        <?php if (empty($receta_del_producto)): ?>
                            <tr><td colspan="3" class="text-center">Esta receta aún no tiene ingredientes.</td></tr>
                        <?php else: ?>
                            <?php foreach ($receta_del_producto as $ingrediente): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ingrediente['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($ingrediente['cantidad_necesaria']) . ' ' . htmlspecialchars($ingrediente['unidad_medida']); ?></td>
                                    <td>
                                        <a href="productos.php?accion=eliminar_ingrediente&id=<?php echo $ingrediente['id']; ?>&producto_id_redirect=<?php echo $producto_a_editar['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('¿Seguro que quieres quitar este ingrediente?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lista de Productos -->
    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-list"></i> Listado de Productos</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark"><tr><th>Imagen</th><th>Nombre</th><th>Precio</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td class="text-center">
                                    <img src="<?php echo !empty($producto['imagen_url']) ? htmlspecialchars($producto['imagen_url']) : 'public/img/placeholder.png'; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                </td>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td>$<?php echo number_format($producto['precio_venta'], 2); ?></td>
                                <td>
                                    <a href="productos.php?accion=editar&id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-warning me-2" title="Editar Producto y Receta"><i class="fas fa-edit"></i></a>
                                    <a href="productos.php?accion=eliminar&id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar Producto" onclick="return confirm('¿Estás seguro? Se eliminará el producto y su receta.');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
mysqli_close($db);
include 'includes/footer.php';
?>