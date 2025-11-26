<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$db = conectarDB();
$response = ['success' => false, 'message' => '', 'ingredients' => [], 'has_stock' => true];

// Verificar si se recibió el ID del producto y la cantidad
if (isset($_POST['producto_id']) && isset($_POST['cantidad'])) {
    $producto_id = (int)$_POST['producto_id'];
    $cantidad = (int)$_POST['cantidad'];
    
    // Obtener los ingredientes de la receta del producto
    $stmt = $db->prepare("SELECT r.subproducto_id, r.cantidad_necesaria, s.nombre, s.stock, s.unidad_medida 
                         FROM recetas r 
                         JOIN subproductos s ON r.subproducto_id = s.id 
                         WHERE r.producto_id = ?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ingredientes_faltantes = [];
    $has_stock = true;
    
    while ($ingrediente = $result->fetch_assoc()) {
        $cantidad_necesaria = $ingrediente['cantidad_necesaria'] * $cantidad;
        $stock_actual = $ingrediente['stock'];
        
        if ($stock_actual < $cantidad_necesaria) {
            $has_stock = false;
            $ingredientes_faltantes[] = [
                'id' => $ingrediente['subproducto_id'],
                'nombre' => $ingrediente['nombre'],
                'stock_actual' => $stock_actual,
                'cantidad_necesaria' => $cantidad_necesaria,
                'unidad_medida' => $ingrediente['unidad_medida']
            ];
        }
    }
    
    $response['success'] = true;
    $response['has_stock'] = $has_stock;
    $response['ingredients'] = $ingredientes_faltantes;
    
    if (!$has_stock) {
        $response['message'] = 'No hay suficiente stock de algunos ingredientes para este producto.';
    }
} else {
    $response['message'] = 'Datos incompletos';
}

echo json_encode($response);