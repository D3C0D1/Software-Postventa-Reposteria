<?php
session_start();
require_once 'config/database.php';

// Inicializar el carrito si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Limpiar el carrito
if (isset($_POST['action']) && $_POST['action'] == 'clear') {
    $_SESSION['cart'] = [];
    $_SESSION['success_message'] = 'El carrito se ha vaciado.';
    header('Location: galeria_venta.php');
    exit;
}

// Añadir producto personalizado al carrito
if (isset($_POST['action']) && $_POST['action'] == 'add_customized') {
    $producto_id = (int)$_POST['producto_id'];
    $cantidad = (int)$_POST['cantidad'];
    $salsas = json_decode($_POST['salsas'], true);
    $adiciones = json_decode($_POST['adiciones'], true);
    $force_add = isset($_POST['force_add']) && $_POST['force_add'] == 'true';
    
    if ($cantidad > 0) {
        // Crear un identificador único para el producto personalizado
        $customization_hash = md5(serialize(['salsas' => $salsas, 'adiciones' => $adiciones]));
        $cart_item_id = 'producto-personalizado-' . $producto_id . '-' . $customization_hash;
        
        // Si el item ya está en el carrito, actualiza la cantidad
        if (isset($_SESSION['cart'][$cart_item_id])) {
            $_SESSION['cart'][$cart_item_id]['cantidad'] += $cantidad;
        } else {
            // Añadir nuevo producto personalizado
            $_SESSION['cart'][$cart_item_id] = [
                'id' => $producto_id,
                'cantidad' => $cantidad,
                'tipo' => 'producto_personalizado',
                'salsas' => $salsas,
                'adiciones' => $adiciones
            ];
        }
        
        $_SESSION['success_message'] = 'Producto personalizado añadido al carrito.';
    } else {
        $_SESSION['error_message'] = 'La cantidad debe ser mayor que cero.';
    }
    
    header('Location: galeria_venta.php');
    exit;
}

// Añadir item al carrito (producto o subproducto)
if (isset($_POST['action']) && $_POST['action'] == 'add' && isset($_POST['producto_id']) && isset($_POST['cantidad']) && isset($_POST['producto_tipo'])) {
    $producto_id = (int)$_POST['producto_id'];
    $cantidad = (int)$_POST['cantidad'];
    $tipo = $_POST['producto_tipo']; // 'producto' o 'subproducto'
    $force_add = isset($_POST['force_add']) && $_POST['force_add'] == 'true';

    // Crear un identificador único para el item en el carrito
    $cart_item_id = $tipo . '-' . $producto_id;

    if ($cantidad > 0) {
        // Si el item ya está en el carrito, actualiza la cantidad
        if (isset($_SESSION['cart'][$cart_item_id])) {
            $_SESSION['cart'][$cart_item_id]['cantidad'] += $cantidad;
        } else {
            // Si no, lo añade con su tipo
            $_SESSION['cart'][$cart_item_id] = ['id' => $producto_id, 'cantidad' => $cantidad, 'tipo' => $tipo];
        }
        $_SESSION['success_message'] = 'Item añadido al carrito.';
    } else {
        $_SESSION['error_message'] = 'La cantidad debe ser mayor que cero.';
    }

    header('Location: galeria_venta.php');
    exit;
}

// Eliminar un item del carrito
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $cart_item_id = $_GET['id'];
    if (isset($_SESSION['cart'][$cart_item_id])) {
        unset($_SESSION['cart'][$cart_item_id]);
        $_SESSION['success_message'] = 'Item eliminado del carrito.';
    }
    header('Location: galeria_venta.php');
    exit;
}

// Redirección por defecto si no hay acción
header('Location: galeria_venta.php');
exit;