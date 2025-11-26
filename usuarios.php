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

// --- LÓGICA DE USUARIOS ---

// 1. PROCESAR ACCIONES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $nombre = filter_var(trim($_POST['nombre']), FILTER_SANITIZE_STRING); // Nuevo campo
    $usuario_nombre = filter_var(trim($_POST['usuario']), FILTER_SANITIZE_STRING);
    $rol = filter_var($_POST['rol'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Validaciones
    if (!$nombre) $errores[] = 'El nombre completo es obligatorio.'; // Nueva validación
    if (!$usuario_nombre) $errores[] = 'El nombre de usuario es obligatorio.';
    if (!$rol || !in_array($rol, ['admin', 'invitado', 'operador'])) $errores[] = 'El rol seleccionado no es válido.';
    if (!$id && !$password) $errores[] = 'La contraseña es obligatoria para nuevos usuarios.';
    if ($id && !empty($password) && strlen($password) < 6) $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.';

    // Validar que el usuario no exista (excepto para el usuario actual)
    $stmt_user = $db->prepare("SELECT id FROM usuarios WHERE usuario = ? AND id != ?");
    $current_id = $id ?? 0;
    $stmt_user->bind_param('si', $usuario_nombre, $current_id);
    $stmt_user->execute();
    if ($stmt_user->get_result()->num_rows > 0) {
        $errores[] = 'El nombre de usuario ya está en uso.';
    }
    $stmt_user->close();

    if (empty($errores)) {
        if ($id) { // Actualizar
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, rol = ?, password = ? WHERE id = ?");
                $stmt->bind_param('ssssi', $nombre, $usuario_nombre, $rol, $hash, $id);
            } else {
                $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, rol = ? WHERE id = ?");
                $stmt->bind_param('sssi', $nombre, $usuario_nombre, $rol, $id);
            }
        } else { // Crear
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuarios (nombre, usuario, rol, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $nombre, $usuario_nombre, $rol, $hash);
        }

        if ($stmt->execute()) {
            $mensaje = $id ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.';
        } else {
            $errores[] = 'Error al guardar el usuario.';
        }
        $stmt->close();
    }
}

// 2. PROCESAR ACCIONES (GET)
$accion_get = $_GET['accion'] ?? '';
$id_get = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if ($accion_get === 'eliminar' && $id_get) {
    // No permitir que un admin se elimine a sí mismo
    if ($id_get == ($_SESSION['id_usuario'] ?? null)) {
        $errores[] = 'No puedes eliminar tu propia cuenta de administrador.';
    } else {
        $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $id_get);
        if ($stmt->execute()) {
            $mensaje = 'Usuario eliminado correctamente.';
        } else {
            $errores[] = 'Error al eliminar el usuario.';
        }
        $stmt->close();
    }
}

// --- OBTENER DATOS PARA LA VISTA ---

$usuarios_query = $db->query("SELECT id, nombre, usuario, rol FROM usuarios ORDER BY usuario ASC");
if (!$usuarios_query) {
    die("Error en la consulta de usuarios: " . $db->error);
}
$usuarios = $usuarios_query->fetch_all(MYSQLI_ASSOC);

$usuario_a_editar = null;
if ($accion_get === 'editar' && $id_get) {
    $stmt = $db->prepare("SELECT id, nombre, usuario, rol FROM usuarios WHERE id = ?");
    $stmt->bind_param('i', $id_get);
    $stmt->execute();
    $usuario_a_editar = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Usuarios</h1>
    <p class="lead">Crea, edita y gestiona los usuarios del sistema.</p>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    <?php foreach ($errores as $error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endforeach; ?>

    <!-- Formulario de Usuario -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-edit"></i>
            <?php echo $usuario_a_editar ? 'Editando Usuario: ' . htmlspecialchars($usuario_a_editar['usuario']) : 'Crear Nuevo Usuario'; ?>
        </div>
        <div class="card-body">
            <form action="usuarios.php<?php echo $usuario_a_editar ? '?accion=editar&id=' . $usuario_a_editar['id'] : ''; ?>" method="POST">
                <input type="hidden" name="accion" value="guardar_usuario">
                <input type="hidden" name="id" value="<?php echo $usuario_a_editar['id'] ?? ''; ?>">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($usuario_a_editar['nombre'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="usuario" class="form-label">Nombre de Usuario (Email)</label>
                        <input type="email" class="form-control" name="usuario" value="<?php echo htmlspecialchars($usuario_a_editar['usuario'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="rol" class="form-label">Rol</label>
                        <select name="rol" class="form-select" required>
                            <option value="invitado" <?php echo (isset($usuario_a_editar['rol']) && $usuario_a_editar['rol'] == 'invitado') ? 'selected' : ''; ?>>Invitado</option>
                            <option value="admin" <?php echo (isset($usuario_a_editar['rol']) && $usuario_a_editar['rol'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="operador" <?php echo (isset($usuario_a_editar['rol']) && $usuario_a_editar['rol'] == 'operador') ? 'selected' : ''; ?>>Operador</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" <?php echo !$usuario_a_editar ? 'required' : ''; ?>>
                        <?php if ($usuario_a_editar): ?>
                            <small class="form-text text-muted">Deja en blanco para no cambiar la contraseña.</small>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                <?php if ($usuario_a_editar): ?>
                    <a href="usuarios.php" class="btn btn-secondary">Cancelar Edición</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users"></i>
            Listado de Usuarios
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Nombre de Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                            <td><span class="badge bg-<?php echo $usuario['rol'] == 'admin' ? 'success' : 'info'; ?>"><?php echo ucfirst($usuario['rol']); ?></span></td>
                            <td>
                                <a href="usuarios.php?accion=editar&id=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <?php if ($usuario['id'] != ($_SESSION['id_usuario'] ?? null)): // No mostrar botón para el usuario actual ?>
                                    <a href="usuarios.php?accion=eliminar&id=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?');">Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>