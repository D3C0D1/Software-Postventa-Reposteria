<?php
require_once 'includes/auth_check.php';
if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/database.php';

$page_title = '';
$is_dashboard_page = true;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$message = '';

// Obtener la configuración actual para usarla si no se actualizan los datos
$result = $conn->query("SELECT * FROM configuracion WHERE id = 1");
$config_actual = $result->fetch_assoc();

// Paletas de colores predefinidas
$palettes = [
    'default' => ['#FFFFFF', '#F8F9FA', '#212529'],
    'dark' => ['#212529', '#343A40', '#F8F9FA'],
    'oceanic' => ['#E0F7FA', '#B2EBF2', '#00796B']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_empresa = !empty(trim($_POST['nombre_empresa'])) ? trim($_POST['nombre_empresa']) : $config_actual['nombre_empresa'];
    $nit = !empty(trim($_POST['nit'])) ? trim($_POST['nit']) : $config_actual['nit'];
    $direccion = !empty(trim($_POST['direccion'])) ? trim($_POST['direccion']) : $config_actual['direccion'];
    $palette_choice = $_POST['palette'] ?? 'default';

    list($color_primario, $color_secundario, $color_texto) = $palettes[$palette_choice];

    // Mantener el logo existente si no se sube uno nuevo
    $logo_url = $config_actual['logo_url'];

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $target_dir = "public/uploads/";
        $target_file = $target_dir . basename($_FILES["logo"]["name"]);
        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $logo_url = $target_file;
        } else {
            $message = '<div class="alert alert-danger">Error al subir el logo.</div>';
        }
    }

    $sql = "UPDATE configuracion SET nombre_empresa = ?, nit = ?, direccion = ?, color_primario = ?, color_secundario = ?, color_texto = ?, logo_url = ? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssss', $nombre_empresa, $nit, $direccion, $color_primario, $color_secundario, $color_texto, $logo_url);

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Configuración actualizada correctamente.</div>';
    } else {
        $message = '<div class="alert alert-danger">Error al actualizar la configuración.</div>';
    }
    $stmt->close();
}

// Obtener la configuración actual
$result = $conn->query("SELECT * FROM configuracion WHERE id = 1");
$config = $result->fetch_assoc();
$conn->close();

include 'includes/header.php';
?>

<div class="container-fluid">
    <?php echo $message; ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Información de la Empresa y Apariencia</h3>
        </div>
        <div class="card-body">
            <form action="configuracion.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="nombre_empresa" class="form-label">Nombre de la Empresa</label>
                    <input type="text" class="form-control" id="nombre_empresa" name="nombre_empresa" value="<?php echo htmlspecialchars($config['nombre_empresa'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nit" class="form-label">NIT</label>
                    <input type="text" class="form-control" id="nit" name="nit" value="<?php echo htmlspecialchars($config['nit'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($config['direccion'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label">Logo de la Empresa</label>
                    <input type="file" class="form-control" id="logo" name="logo">
                    <input type="hidden" name="existing_logo_url" value="<?php echo htmlspecialchars($config['logo_url'] ?? ''); ?>">
                    <?php if (!empty($config['logo_url'])): ?>
                        <img src="<?php echo htmlspecialchars($config['logo_url']); ?>" alt="Logo actual" style="max-width: 150px; margin-top: 10px;">
                    <?php endif; ?>
                </div>

                <h4 class="mt-4">Paleta de Colores</h4>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="palette" id="palette_default" value="default" <?php echo ($config['color_primario'] == '#FFFFFF') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="palette_default">Predeterminada</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="palette" id="palette_dark" value="dark" <?php echo ($config['color_primario'] == '#212529') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="palette_dark">Modo Oscuro</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="palette" id="palette_oceanic" value="oceanic" <?php echo ($config['color_primario'] == '#E0F7FA') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="palette_oceanic">Oceánico</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>