<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Cambia esto si tienes una contraseña
define('DB_NAME', 'kalua_db');

// Incluir el manejador de errores si existe
$error_handler_file = __DIR__ . '/../includes/error_handler.php';
if (file_exists($error_handler_file)) {
    require_once $error_handler_file;
}

function conectarDB() {
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conexion) {
        echo "Error: No se pudo conectar a MySQL.";
        echo "errno de depuración: " . mysqli_connect_errno();
        echo "error de depuración: " . mysqli_connect_error();
        exit;
    }
    return $conexion;
}
?>