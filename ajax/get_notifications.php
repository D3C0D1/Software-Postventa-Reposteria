<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$db = conectarDB();

// Función para obtener productos y subproductos con problemas de stock
function obtenerProductosConProblemasStock($db) {
    $notificaciones = [];
    $total = 0;
    
    // 1. Subproductos con stock bajo (mayor a 0 pero menor o igual a 5)
    $query_bajo = "SELECT id, nombre, stock, unidad_medida FROM subproductos WHERE stock <= 5 AND stock > 0 ORDER BY stock ASC";
    $result_bajo = $db->query($query_bajo);
    
    if ($result_bajo) {
        while ($subproducto = $result_bajo->fetch_assoc()) {
            $notificaciones[] = [
                'mensaje' => "Stock bajo: {$subproducto['nombre']} ({$subproducto['stock']} {$subproducto['unidad_medida']})",
                'url' => "../inventario.php?id={$subproducto['id']}&action=edit",
                'tipo' => 'bajo'
            ];
            $total++;
        }
    }
    
    // 2. Subproductos agotados (stock = 0)
    $query_agotado = "SELECT id, nombre, stock, unidad_medida FROM subproductos WHERE stock = 0 ORDER BY nombre ASC";
    $result_agotado = $db->query($query_agotado);
    
    if ($result_agotado) {
        while ($subproducto = $result_agotado->fetch_assoc()) {
            $notificaciones[] = [
                'mensaje' => "AGOTADO: {$subproducto['nombre']} (0 {$subproducto['unidad_medida']})",
                'url' => "../inventario.php?id={$subproducto['id']}&action=edit",
                'tipo' => 'agotado'
            ];
            $total++;
        }
    }
    
    // 3. Subproductos con stock negativo (error en el inventario)
    $query_negativo = "SELECT id, nombre, stock, unidad_medida FROM subproductos WHERE stock < 0 ORDER BY stock ASC";
    $result_negativo = $db->query($query_negativo);
    
    if ($result_negativo) {
        while ($subproducto = $result_negativo->fetch_assoc()) {
            $notificaciones[] = [
                'mensaje' => "ERROR DE STOCK: {$subproducto['nombre']} ({$subproducto['stock']} {$subproducto['unidad_medida']})",
                'url' => "../inventario.php?id={$subproducto['id']}&action=edit",
                'tipo' => 'error'
            ];
            $total++;
        }
    }
    
    // 4. Productos con estado inactivo (opcional)
    $query_inactivos = "SELECT id, nombre FROM productos WHERE estado = 'inactivo' ORDER BY nombre ASC";
    $result_inactivos = $db->query($query_inactivos);
    
    if ($result_inactivos) {
        while ($producto = $result_inactivos->fetch_assoc()) {
            $notificaciones[] = [
                'mensaje' => "Producto inactivo: {$producto['nombre']}",
                'url' => '../productos.php',
                'tipo' => 'inactivo'
            ];
            $total++;
        }
    }
    
    return [
        'notificaciones' => $notificaciones,
        'total' => $total
    ];
}

// Obtener todas las notificaciones
$resultado = obtenerProductosConProblemasStock($db);
$notificaciones = $resultado['notificaciones'];
$total = $resultado['total'];

$db->close();

echo json_encode([
    'notificaciones' => $notificaciones,
    'total' => $total
]);
?>