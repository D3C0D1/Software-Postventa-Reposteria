<?php
require_once __DIR__ . '/../config/database.php';
$db = conectarDB();
?>
<div class="cart-container">
    <h3>Carrito de Compras</h3>
    <?php
    if (empty($_SESSION['cart'])) {
        echo "<p>El carrito está vacío.</p>";
    } else {
        $total_venta = 0;
        echo "<ul class='list-group'>";
        
        foreach ($_SESSION['cart'] as $cart_item_id => $item) {
            $producto_id = $item['id'];
            $cantidad = $item['cantidad'];
            $tipo = $item['tipo'];
            
            if ($tipo === 'producto' || $tipo === 'producto_personalizado') {
                // Obtener información del producto
                $stmt = $db->prepare("SELECT nombre, precio_venta FROM productos WHERE id = ?");
                $stmt->bind_param("i", $producto_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $producto = $resultado->fetch_assoc();
                
                if ($producto) {
                    $subtotal = $producto['precio_venta'] * $cantidad;
                    $total_venta += $subtotal;
                    
                    echo "<li class='list-group-item'>";
                    echo "<div class='d-flex justify-content-between align-items-start'>";
                    echo "<div>";
                    echo "<h6 class='mb-1'>" . htmlspecialchars($producto['nombre']) . " (x{$cantidad})</h6>";
                    
                    // Si es producto personalizado, mostrar las personalizaciones
                    if ($tipo === 'producto_personalizado') {
                        echo "<small class='text-muted'>Producto Personalizado</small><br>";
                        
                        // Mostrar salsas seleccionadas
                        if (!empty($item['salsas'])) {
                            echo "<small><strong>Salsas:</strong> ";
                            $salsas_nombres = [];
                            $salsas_count = [];
                            
                            // Contar las salsas
                            foreach ($item['salsas'] as $salsa) {
                                $salsa_id = $salsa['id'];
                                if (!isset($salsas_count[$salsa_id])) {
                                    $salsas_count[$salsa_id] = ['nombre' => $salsa['nombre'], 'cantidad' => 0];
                                }
                                $salsas_count[$salsa_id]['cantidad']++;
                            }
                            
                            // Mostrar salsas con sus cantidades
                            foreach ($salsas_count as $salsa_data) {
                                $salsas_nombres[] = $salsa_data['nombre'] . " (x" . $salsa_data['cantidad'] . ")";
                            }
                            echo implode(", ", $salsas_nombres) . "</small><br>";
                        }
                        
                        // Mostrar adiciones seleccionadas
                        if (!empty($item['adiciones'])) {
                            echo "<small><strong>Adiciones:</strong> ";
                            $adiciones_nombres = [];
                            $adiciones_count = [];
                            
                            // Contar las adiciones
                            foreach ($item['adiciones'] as $adicion) {
                                $adicion_id = $adicion['id'];
                                if (!isset($adiciones_count[$adicion_id])) {
                                    $adiciones_count[$adicion_id] = ['nombre' => $adicion['nombre'], 'cantidad' => 0];
                                }
                                $adiciones_count[$adicion_id]['cantidad']++;
                            }
                            
                            // Mostrar adiciones con sus cantidades
                            foreach ($adiciones_count as $adicion_data) {
                                $adiciones_nombres[] = $adicion_data['nombre'] . " (x" . $adicion_data['cantidad'] . ")";
                            }
                            echo implode(", ", $adiciones_nombres) . "</small>";
                        }
                    }
                    
                    echo "</div>";
                    echo "<div class='text-end'>";
                    echo "<span class='fw-bold'>$" . number_format($subtotal, 2) . "</span><br>";
                    echo "<a href='cart_handler.php?action=remove&id={$cart_item_id}' class='btn btn-danger btn-sm'>&times;</a>";
                    echo "</div>";
                    echo "</div>";
                    echo "</li>";
                }
                $stmt->close();
                
            } elseif ($tipo === 'subproducto') {
                // Obtener información del subproducto
                $stmt = $db->prepare("SELECT nombre, precio FROM subproductos WHERE id = ?");
                $stmt->bind_param("i", $producto_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $subproducto = $resultado->fetch_assoc();
                
                if ($subproducto) {
                    $subtotal = $subproducto['precio'] * $cantidad;
                    $total_venta += $subtotal;
                    
                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                    echo htmlspecialchars($subproducto['nombre']) . " (x{$cantidad})";
                    echo "<span>$" . number_format($subtotal, 2) . "</span>";
                    echo "<a href='cart_handler.php?action=remove&id={$cart_item_id}' class='btn btn-danger btn-sm'>&times;</a>";
                    echo "</li>";
                }
                $stmt->close();
            }
        }
        
        echo "</ul>";
        echo "<hr>";
        echo "<h4>Total: $" . number_format($total_venta, 2) . "</h4>";
        echo "<div class='mt-3'>";
        echo "<a href='checkout.php' class='btn btn-success'>Finalizar Compra</a>";
        echo "<form action='cart_handler.php' method='post' style='display: inline-block; margin-left: 10px;'>";
        echo "<input type='hidden' name='action' value='clear'>";
        echo "<button type='submit' class='btn btn-warning'>Vaciar Carrito</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>
</div>
<?php $db->close(); ?>