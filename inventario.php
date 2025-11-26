<?php
$is_dashboard_page = true;
$page_title = '';
require_once 'includes/auth_check.php';
require_once 'config/database.php';

if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$db = conectarDB();
$errores = [];
$mensaje = '';

// Directorio para las imágenes de subproductos
define('UPLOAD_SUBPRODUCTOS', __DIR__ . '/public/uploads/subproductos/');

// Variables para el formulario de edición
$edit_mode = false;
$subproducto_a_editar = ['id' => '', 'nombre' => '', 'precio' => '', 'imagen' => '', 'stock' => '', 'unidad_medida' => ''];

// --- LÓGICA PARA PROCESAR ACCIONES ---

// 1. ELIMINAR SUBPRODUCTO
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
    $id_a_eliminar = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id_a_eliminar) {
        // Opcional: Eliminar la imagen asociada
        $stmt_img = $db->prepare("SELECT imagen FROM subproductos WHERE id = ?");
        $stmt_img->bind_param('i', $id_a_eliminar);
        $stmt_img->execute();
        $res_img = $stmt_img->get_result()->fetch_assoc();
        if ($res_img && !empty($res_img['imagen']) && file_exists(UPLOAD_SUBPRODUCTOS . $res_img['imagen'])) {
            unlink(UPLOAD_SUBPRODUCTOS . $res_img['imagen']);
        }
        $stmt_img->close();

        $stmt = $db->prepare("DELETE FROM subproductos WHERE id = ?");
        $stmt->bind_param('i', $id_a_eliminar);
        if ($stmt->execute()) {
            $mensaje = 'Subproducto eliminado correctamente.';
        } else {
            $errores[] = 'Error al eliminar el subproducto.';
        }
        $stmt->close();
    }
}

// 2. MOSTRAR FORMULARIO DE EDICIÓN
if (isset($_GET['accion']) && $_GET['accion'] == 'editar' && isset($_GET['id'])) {
    $id_a_editar = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id_a_editar) {
        $stmt = $db->prepare("SELECT * FROM subproductos WHERE id = ?");
        $stmt->bind_param('i', $id_a_editar);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            $subproducto_a_editar = $resultado->fetch_assoc();
            $edit_mode = true;
        }
        $stmt->close();
    }
}

// 3. PROCESAR CREACIÓN O ACTUALIZACIÓN (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = $_POST['id'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $precio = $_POST['precio'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $unidad_medida = $_POST['unidad_medida'] ?? '';
    $categoria = $_POST['categoria'] ?? 'ingrediente'; // Nueva línea

    // Validaciones
    if (empty($nombre)) {
        $errores[] = 'El nombre del subproducto es obligatorio.';
    }
    if ($precio < 0) {
        $errores[] = 'El precio no puede ser negativo.';
    }
    if ($stock < 0) {
        $errores[] = 'El stock no puede ser negativo.';
    }
    if (!in_array($unidad_medida, ['unidad', 'gramos', 'kilogramos'])) {
        $errores[] = 'Unidad de medida inválida.';
    }
    if (!in_array($categoria, ['ingrediente', 'salsa'])) { // Nueva validación
        $errores[] = 'Categoría inválida.';
    }
    if (!$nombre) $errores[] = 'El nombre es obligatorio.';
    if ($precio === false || $precio < 0) $errores[] = 'El precio debe ser un número válido.';
    if ($stock === false || $stock < 0) $errores[] = 'El stock debe ser un número válido y no negativo.';
    if (empty($unidad_medida)) $errores[] = 'Debe seleccionar una unidad de medida.';

    // Procesamiento de la imagen
    $nombre_imagen = $_POST['imagen_actual'] ?? '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = $_FILES['imagen'];
        $nombre_imagen = md5(uniqid(rand(), true)) . ".jpg"; // Nombre único
        if (!is_dir(UPLOAD_SUBPRODUCTOS)) {
            mkdir(UPLOAD_SUBPRODUCTOS, 0777, true);
        }
        move_uploaded_file($imagen['tmp_name'], UPLOAD_SUBPRODUCTOS . $nombre_imagen);

        // Si es una actualización y había una imagen anterior, borrarla
        if ($accion === 'actualizar' && !empty($_POST['imagen_actual'])) {
            $img_anterior = UPLOAD_SUBPRODUCTOS . $_POST['imagen_actual'];
            if (file_exists($img_anterior)) {
                unlink($img_anterior);
            }
        }
    }

    if (empty($errores)) {
        if ($accion === 'crear') {
            $stmt = $db->prepare("INSERT INTO subproductos (nombre, precio, imagen, stock, unidad_medida, categoria) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sdsdss', $nombre, $precio, $nombre_imagen, $stock, $unidad_medida, $categoria);
            if ($stmt->execute()) {
                $mensaje = 'Subproducto creado correctamente.';
            } else {
                $errores[] = 'Error al crear el subproducto: ' . $stmt->error;
            }
            $stmt->close();
        } elseif ($accion === 'actualizar' && $id) {
            $stmt = $db->prepare("UPDATE subproductos SET nombre = ?, precio = ?, imagen = ?, stock = ?, unidad_medida = ?, categoria = ? WHERE id = ?");
            $stmt->bind_param('sdsdssi', $nombre, $precio, $nombre_imagen, $stock, $unidad_medida, $categoria, $id);
            if ($stmt->execute()) {
                $mensaje = 'Subproducto actualizado correctamente.';
                $edit_mode = false;
                $subproducto_a_editar = ['id' => '', 'nombre' => '', 'precio' => '', 'imagen' => '', 'stock' => '', 'unidad_medida' => '', 'categoria' => ''];
            } else {
                $errores[] = 'Error al actualizar el subproducto: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// --- OBTENER LISTA DE SUBPRODUCTOS (CON BÚSQUEDA) ---
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$query = "SELECT * FROM subproductos";
if ($search_term) {
    $query .= " WHERE nombre LIKE ?";
}
$query .= " ORDER BY nombre ASC";

$stmt_lista = $db->prepare($query);
if ($search_term) {
    $like_term = "%{$search_term}%";
    $stmt_lista->bind_param('s', $like_term);
}
$stmt_lista->execute();
$resultado_lista = $stmt_lista->get_result();

?>

<?php include 'includes/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Inventario</h1>
    <p class="lead">Administra los subproductos o ingredientes.</p>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?php echo $mensaje; ?></div>
    <?php endif; ?>
    <?php foreach ($errores as $error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endforeach; ?>

    <!-- Formulario de Creación / Edición -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus-circle"></i>
            <?php echo $edit_mode ? 'Editar Subproducto' : 'Añadir Nuevo Subproducto'; ?>
        </div>
        <div class="card-body">
            <form action="inventario.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="<?php echo $edit_mode ? 'actualizar' : 'crear'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($subproducto_a_editar['id']); ?>">
                    <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($subproducto_a_editar['imagen']); ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nombre" class="form-label">Nombre del Subproducto</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($subproducto_a_editar['nombre']); ?>" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="<?php echo htmlspecialchars($subproducto_a_editar['precio']); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="imagen" class="form-label">Imagen</label>
                        <input type="file" class="form-control" id="imagen" name="imagen" accept="image/jpeg, image/png">
                        <?php if ($edit_mode && !empty($subproducto_a_editar['imagen'])): ?>
                            <img src="public/uploads/subproductos/<?php echo htmlspecialchars($subproducto_a_editar['imagen']); ?>" width="100" class="mt-2">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="stock" class="form-label">Stock Actual</label>
                        <input type="number" step="1" min="0" class="form-control" id="stock" name="stock" value="<?php echo htmlspecialchars((int)$subproducto_a_editar['stock']); ?>" required>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="form-label">Unidad de Medida</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="unidad_medida" id="unidad" value="unidad" <?php echo ($subproducto_a_editar['unidad_medida'] == 'unidad') ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="unidad">Unidad</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="unidad_medida" id="gramos" value="gramos" <?php echo ($subproducto_a_editar['unidad_medida'] == 'gramos') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="gramos">Gramos</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="unidad_medida" id="kilogramos" value="kilogramos" <?php echo ($subproducto_a_editar['unidad_medida'] == 'kilogramos') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="kilogramos">Kilogramos</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Categoría</label>
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categoria" id="categoria" value="salsa" <?php echo (isset($subproducto_a_editar['categoria']) && $subproducto_a_editar['categoria'] == 'salsa') ? 'checked' : ''; ?> onchange="toggleCategoria(this)">
                                <label class="form-check-label" for="categoria_salsa">
                                    Es una salsa
                                </label>
                            </div>
                            <small class="form-text text-muted">Si no está marcado, se considerará como ingrediente</small>
                        </div>
                    </div>
                    <div class="col-md-8 d-flex align-items-end mb-3">
                        <button type="submit" class="btn btn-primary w-50"><?php echo $edit_mode ? 'Actualizar' : 'Crear'; ?></button>
                        <?php if ($edit_mode): ?>
                            <a href="inventario.php" class="btn btn-secondary w-50 ms-2">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Subproductos -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-boxes"></i>
            Listado de Subproductos
        </div>
        <div class="card-body">
            <!-- Barra de Búsqueda -->
            <form action="inventario.php" method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar por nombre..." name="q" value="<?php echo htmlspecialchars($search_term); ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Unidad de Medida</th>
                            <th>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($subproducto = $resultado_lista->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subproducto['id']); ?></td>
                            <td>
                                <?php if (!empty($subproducto['imagen'])): ?>
                                    <img src="public/uploads/subproductos/<?php echo htmlspecialchars($subproducto['imagen']); ?>" alt="<?php echo htmlspecialchars($subproducto['nombre']); ?>" width="50">
                                <?php else: ?>
                                    <small>Sin imagen</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($subproducto['nombre']); ?></td>
                            <td>$<?php echo number_format($subproducto['precio'], 2); ?></td>
                            <td><?php echo htmlspecialchars($subproducto['stock']); ?></td>
                            <td><?php echo htmlspecialchars($subproducto['unidad_medida']); ?></td>
                            <td>
                                <span class="badge <?php echo ($subproducto['categoria'] == 'salsa') ? 'bg-warning' : 'bg-info'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($subproducto['categoria'] ?? 'ingrediente')); ?>
                                </span>
                            </td>
                            <td>
                                <a href="inventario.php?accion=editar&id=<?php echo $subproducto['id']; ?>" class="btn btn-sm btn-warning me-2" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="inventario.php?accion=eliminar&id=<?php echo $subproducto['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este subproducto?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCategoria(checkbox) {
    // Solo permitir un checkbox marcado a la vez
    if (checkbox.checked) {
        checkbox.value = 'salsa';
    } else {
        checkbox.value = 'ingrediente';
    }
}
</script>

<?php 
$stmt_lista->close();
mysqli_close($db);
include 'includes/footer.php'; 
?>