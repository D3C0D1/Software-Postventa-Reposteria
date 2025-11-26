<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth_check.php';

header('Content-Type: application/json');

// Solo los administradores pueden realizar estas acciones
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'delete_sale') {
    $saleId = isset($_POST['sale_id']) ? (int)$_POST['sale_id'] : 0;

    if ($saleId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de venta no válido.']);
        exit;
    }

    $conexion = conectarDB();
    mysqli_begin_transaction($conexion);

    try {
        // 1. Eliminar los detalles de la venta
        $stmt1 = mysqli_prepare($conexion, "DELETE FROM venta_detalles WHERE venta_id = ?");
        mysqli_stmt_bind_param($stmt1, 'i', $saleId);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);

        // 2. Eliminar la venta principal
        $stmt2 = mysqli_prepare($conexion, "DELETE FROM ventas WHERE id = ?");
        mysqli_stmt_bind_param($stmt2, 'i', $saleId);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        mysqli_commit($conexion);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la venta: ' . $e->getMessage()]);
    }
    mysqli_close($conexion);
    exit;
}

// Si no se reconoce la acción
echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
?>
require_once 'config/database.php';
header('Content-Type: application/json');

$db = conectarDB();
$response = ['success' => false, 'message' => 'Acción no válida'];
$accion = $_REQUEST['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'crear_subproducto') {
    if ($_POST['accion'] === 'crear_subproducto') {
        $nombre = filter_var(trim($_POST['nombre']), FILTER_SANITIZE_STRING);
        $stock = filter_var($_POST['stock'], FILTER_VALIDATE_FLOAT);
        $unidad = filter_var(trim($_POST['unidad_medida']), FILTER_SANITIZE_STRING);

        if ($nombre && $stock !== false && $unidad) {
            $stmt = $db->prepare("INSERT INTO subproductos (nombre, stock, unidad_medida) VALUES (?, ?, ?)");
            $stmt->bind_param('sds', $nombre, $stock, $unidad);
            if ($stmt->execute()) {
                $new_id = $db->insert_id;
                $response['success'] = true;
                $response['message'] = 'Subproducto creado.';
                $response['subproducto'] = ['id' => $new_id, 'nombre' => $nombre, 'unidad_medida' => $unidad];
            } else {
                $response['message'] = 'Error en la base de datos.';
            }
        } else {
            $response['message'] = 'Datos inválidos.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'listar_imagenes') {
    $directorio_imagenes = 'public/uploads/';
    $imagenes = glob($directorio_imagenes . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    
    // Limpiar las rutas para que sean relativas y correctas para la web
    $urls_imagenes = array_map(function($path) {
        return str_replace('\\', '/', $path); // Compatible con Windows y Linux
    }, $imagenes);

    echo json_encode($urls_imagenes);
    exit; // Salir para no enviar el $response por defecto
}

echo json_encode($response);
if ($db) {
    mysqli_close($db);
}
?>