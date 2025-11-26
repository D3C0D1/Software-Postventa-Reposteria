<?php
// Desactivar la visualización de errores
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';

/**
 * Manejador personalizado de errores
 */
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    // Mapear el número de error a un tipo legible
    $error_type = match($errno) {
        E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => 'ERROR',
        E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => 'WARNING',
        E_NOTICE, E_USER_NOTICE => 'NOTICE',
        E_DEPRECATED, E_USER_DEPRECATED => 'DEPRECATED',
        E_PARSE => 'PARSE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE',
        default => 'UNKNOWN'
    };
    
    // Obtener la IP del cliente
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    // Obtener el ID del usuario si está logueado
    $usuario_id = null;
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
        $usuario_id = $_SESSION['user_id'];
    }
    
    try {
        // Conectar a la base de datos
        $db = conectarDB();
        
        // Preparar la consulta
        $stmt = $db->prepare("INSERT INTO error_logs (tipo, mensaje, archivo, linea, ip, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssisi', $error_type, $errstr, $errfile, $errline, $ip, $usuario_id);
        $stmt->execute();
        $stmt->close();
        $db->close();
    } catch (Exception $e) {
        // Si hay un error al registrar, escribir en un archivo de respaldo
        $log_file = __DIR__ . '/../logs/php_errors.log';
        $log_dir = dirname($log_file);
        
        // Crear directorio de logs si no existe
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        // Escribir en el archivo de log
        $log_message = date('[Y-m-d H:i:s]') . " {$error_type}: {$errstr} in {$errfile} on line {$errline}\n";
        error_log($log_message, 3, $log_file);
    }
    
    // Devolver false para que PHP maneje el error internamente si es necesario
    return false;
}

// Registrar el manejador personalizado
set_error_handler('custom_error_handler');

/**
 * Manejador de excepciones no capturadas
 */
function exception_handler($exception) {
    custom_error_handler(
        E_ERROR,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    );
}

// Registrar el manejador de excepciones
set_exception_handler('exception_handler');

/**
 * Manejador para errores fatales
 */
function fatal_error_handler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        custom_error_handler(
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );
    }
}

// Registrar el manejador de errores fatales
register_shutdown_function('fatal_error_handler');
?>