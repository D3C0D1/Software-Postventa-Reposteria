<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

$db = conectarDB();

// Verificar si el carrito existe y no está vacío
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['error_message'] = 'No se puede procesar una venta con el carrito vacío.';
    header('Location: galeria_venta.php');
    exit;
}

// Función para obtener detalles de los items del carrito
function getCartDetails($cart, $db) {
    $details = ['items' => [], 'total' => 0];
    $product_ids = [];
    $subproduct_ids = [];

    foreach ($cart as $cart_item_id => $item) {
        if ($item['tipo'] === 'producto' || $item['tipo'] === 'producto_personalizado') {
            $product_ids[] = $item['id'];
        } elseif ($item['tipo'] === 'subproducto') {
            $subproduct_ids[] = $item['id'];
        }
    }

    // Obtener detalles de productos (normales y personalizados)
    if (!empty($product_ids)) {
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $stmt = $db->prepare("SELECT id, nombre, precio_venta as precio FROM productos WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        while ($data = $resultado->fetch_assoc()) {
            // Buscar el item en el carrito (puede ser producto normal o personalizado)
            foreach ($cart as $cart_item_id => $cart_item) {
                if (($cart_item['tipo'] === 'producto' || $cart_item['tipo'] === 'producto_personalizado') && $cart_item['id'] == $data['id']) {
                    $cantidad = $cart_item['cantidad'];
                    $item_data = [
                        'id' => $data['id'], 
                        'nombre' => $data['nombre'], 
                        'cantidad' => $cantidad, 
                        'precio' => $data['precio'], 
                        'tipo' => $cart_item['tipo']
                    ];
                    
                    // Si es producto personalizado, agregar las personalizaciones
                    if ($cart_item['tipo'] === 'producto_personalizado') {
                        $item_data['salsas'] = $cart_item['salsas'] ?? [];
                        $item_data['adiciones'] = $cart_item['adiciones'] ?? [];
                    }
                    
                    $details['items'][$cart_item_id] = $item_data;
                    $details['total'] += $cantidad * $data['precio'];
                    break;
                }
            }
        }
        $stmt->close();
    }

    // Obtener detalles de subproductos
    if (!empty($subproduct_ids)) {
        $placeholders = implode(',', array_fill(0, count($subproduct_ids), '?'));
        $stmt = $db->prepare("SELECT id, nombre, precio FROM subproductos WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($subproduct_ids)), ...$subproduct_ids);
        $stmt->execute();
        $resultado = $stmt->get_result();
        while ($data = $resultado->fetch_assoc()) {
            $cart_item_id = 'subproducto-' . $data['id'];
            $cantidad = $cart[$cart_item_id]['cantidad'];
            $details['items'][$cart_item_id] = ['id' => $data['id'], 'nombre' => $data['nombre'], 'cantidad' => $cantidad, 'precio' => $data['precio'], 'tipo' => 'subproducto'];
            $details['total'] += $cantidad * $data['precio'];
        }
        $stmt->close();
    }

    return $details;
}

// Si se envía el formulario con los datos del cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_cliente = $_POST['nombre_cliente'] ?? 'Cliente General';
    $detalle_venta = $_POST['detalle_venta'] ?? '';

    $db->begin_transaction();

    try {
        $cartDetails = getCartDetails($_SESSION['cart'], $db);
        $total_venta = $cartDetails['total'];

        // 2. Insertar en la tabla 'ventas'
        $usuario_id = $_SESSION['user_id'];
        $stmt = $db->prepare("INSERT INTO ventas (usuario_id, nombre_cliente, detalle_venta, total, fecha) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("issd", $usuario_id, $nombre_cliente, $detalle_venta, $total_venta);
        $stmt->execute();
        $venta_id = $db->insert_id;
        $stmt->close();

        // 3. Insertar en la tabla 'venta_detalles' y actualizar stock
        if (!empty($cartDetails['items'])) {
            $stmt_detalle = $db->prepare("INSERT INTO venta_detalles (venta_id, producto_id, subproducto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");
            $stmt_update_stock = $db->prepare("UPDATE subproductos SET stock = stock - ? WHERE id = ?");
            $stmt_receta = $db->prepare("SELECT subproducto_id, cantidad_necesaria FROM recetas WHERE producto_id = ?");

            foreach ($cartDetails['items'] as $item) {
                $producto_id_bind = null;
                $subproducto_id_bind = null;

                if ($item['tipo'] === 'producto' || $item['tipo'] === 'producto_personalizado') {
                    $producto_id_bind = $item['id'];
                    
                    // Descontar ingredientes de la receta del inventario
                    $stmt_receta->bind_param("i", $item['id']);
                    $stmt_receta->execute();
                    $receta_result = $stmt_receta->get_result();
                    while ($ingrediente = $receta_result->fetch_assoc()) {
                        $cantidad_a_descontar = $ingrediente['cantidad_necesaria'] * $item['cantidad'];
                        $stmt_update_stock->bind_param("di", $cantidad_a_descontar, $ingrediente['subproducto_id']);
                        if (!$stmt_update_stock->execute()) {
                            throw new Exception("Error al actualizar el stock del ingrediente ID: " . $ingrediente['subproducto_id']);
                        }
                    }
                    
                    // Si es producto personalizado, descontar también las salsas y adiciones seleccionadas
                    if ($item['tipo'] === 'producto_personalizado') {
                        // Descontar salsas
                        if (!empty($item['salsas'])) {
                            foreach ($item['salsas'] as $salsa) {
                                $cantidad_a_descontar = $item['cantidad']; // Una salsa por producto
                                $stmt_update_stock->bind_param("di", $cantidad_a_descontar, $salsa['id']);
                                if (!$stmt_update_stock->execute()) {
                                    throw new Exception("Error al actualizar el stock de la salsa ID: " . $salsa['id']);
                                }
                            }
                        }
                        
                        // Descontar adiciones
                        if (!empty($item['adiciones'])) {
                            foreach ($item['adiciones'] as $adicion) {
                                $cantidad_a_descontar = $item['cantidad']; // Una adición por producto
                                $stmt_update_stock->bind_param("di", $cantidad_a_descontar, $adicion['id']);
                                if (!$stmt_update_stock->execute()) {
                                    throw new Exception("Error al actualizar el stock de la adición ID: " . $adicion['id']);
                                }
                            }
                        }
                    }

                } else { // Es un subproducto (adición)
                    $subproducto_id_bind = $item['id'];
                    
                    // Descontar la adición del inventario
                    $cantidad_a_descontar = $item['cantidad'];
                    $stmt_update_stock->bind_param("di", $cantidad_a_descontar, $item['id']);
                    if (!$stmt_update_stock->execute()) {
                        throw new Exception("Error al actualizar el stock de la adición ID: " . $item['id']);
                    }
                }
                
                // Insertar el detalle de la venta
                $stmt_detalle->bind_param("iiiid", $venta_id, $producto_id_bind, $subproducto_id_bind, $item['cantidad'], $item['precio']);
                if (!$stmt_detalle->execute()) {
                    throw new Exception("Error al insertar detalle de venta: " . $stmt_detalle->error);
                }
            }
            $stmt_detalle->close();
            $stmt_update_stock->close();
            $stmt_receta->close();
        }

        $db->commit();

        unset($_SESSION['cart']);
        $_SESSION['success_message'] = '¡Venta realizada con éxito!';
        header('Location: galeria_venta.php?venta_id=' . $venta_id);
        exit;

    } catch (Exception $e) {
        $db->rollback();
        $_SESSION['error_message'] = 'Error al procesar la venta: ' . $e->getMessage();
        header('Location: checkout.php');
        exit;
    }

} else {
    // Mostrar el formulario para los datos del cliente
    $is_dashboard_page = true;
    $page_title = 'Finalizar Compra';
    require_once 'includes/header.php';
 
    $cartDetails = getCartDetails($_SESSION['cart'], $db);
    $items_resumen = $cartDetails['items'];
    $total_resumen = $cartDetails['total'];
?>

<div class="container-fluid px-4" style="margin-top: 2rem; margin-bottom: 2rem;">
    <div class="col-lg-5 col-md-7 col-sm-10 mx-auto">
        <div class="card shadow-lg border-0 rounded-lg">
            <div class="card-header bg-primary text-white text-center">
                <h3 class="my-2"><i class="fas fa-shopping-cart"></i> Finalizar Compra</h3>
            </div>
            <div class="card-body p-4">
                
                <!-- Resumen del Pedido -->
                <div class="mb-4">
                    <h5 class="text-center">Resumen del Pedido</h5>
                    
                    <h6 class="mt-3">Productos</h6>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($items_resumen as $item): ?>
                            <?php if ($item['tipo'] === 'producto' || $item['tipo'] === 'producto_personalizado'): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['nombre']); ?> <span class="text-success">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></span></h6>
                                            <?php if ($item['tipo'] === 'producto_personalizado'): ?>
                                                <small class="text-muted">Producto Personalizado</small><br>
                                                <?php if (!empty($item['salsas'])): ?>
                                                    <small><strong>Salsas:</strong> 
                                                    <?php 
                                                    $salsas_nombres = [];
                                                    $salsas_count = [];
                                                    foreach ($item['salsas'] as $salsa) {
                                                        if (!isset($salsas_count[$salsa['id']])) {
                                                            $salsas_count[$salsa['id']] = ['nombre' => $salsa['nombre'], 'cantidad' => 0];
                                                        }
                                                        $salsas_count[$salsa['id']]['cantidad']++;
                                                    }
                                                    foreach ($salsas_count as $salsa_data) {
                                                        $salsas_nombres[] = $salsa_data['nombre'] . " (x" . $salsa_data['cantidad'] . ")";
                                                    }
                                                    echo implode(", ", $salsas_nombres);
                                                    ?></small><br>
                                                <?php endif; ?>
                                                <?php if (!empty($item['adiciones'])): ?>
                                                    <small><strong>Adiciones:</strong> 
                                                    <?php 
                                                    $adiciones_nombres = [];
                                                    $adiciones_count = [];
                                                    foreach ($item['adiciones'] as $adicion) {
                                                        if (!isset($adiciones_count[$adicion['id']])) {
                                                            $adiciones_count[$adicion['id']] = ['nombre' => $adicion['nombre'], 'cantidad' => 0];
                                                        }
                                                        $adiciones_count[$adicion['id']]['cantidad']++;
                                                    }
                                                    foreach ($adiciones_count as $adicion_data) {
                                                        $adiciones_nombres[] = $adicion_data['nombre'] . " (x" . $adicion_data['cantidad'] . ")";
                                                    }
                                                    echo implode(", ", $adiciones_nombres);
                                                    ?></small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-secondary rounded-pill">x<?php echo $item['cantidad']; ?></span>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>

                    <h6 class="mt-3">Adiciones</h6>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($items_resumen as $item): ?>
                            <?php if ($item['tipo'] === 'subproducto'): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($item['nombre']); ?> <span class="text-success">$<?php echo number_format($item['precio'], 0, ',', '.'); ?></span>
                                    <span class="badge bg-secondary rounded-pill">x<?php echo $item['cantidad']; ?></span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>

                    <div class="text-center">
                        <h4>Total: <span class="fw-bold text-success">$<?php echo number_format($total_resumen, 0, ',', '.'); ?></span></h4>
                    </div>
                </div>
                <hr>

                <!-- Formulario de Datos del Cliente -->
                <form action="checkout.php" method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="nombre_cliente" class="form-label">Nombre del Cliente</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" placeholder="Nombre para la factura" value="Cliente General">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="detalle_venta" class="form-label">Notas Adicionales (Opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                            <textarea class="form-control" id="detalle_venta" name="detalle_venta" rows="2" placeholder="Instrucciones especiales..."></textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="galeria_venta.php" class="btn btn-outline-danger"><i class="fas fa-times-circle"></i> Cancelar</a>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
    require_once 'includes/footer.php';
}

if ($db) {
    $db->close();
}
?>