<?php
$is_dashboard_page = true;
$page_title = 'Carrito de Compras';
require_once 'includes/auth_check.php';

// Incluir el encabezado del dashboard
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <?php 
            // Incluir la vista del carrito de compras
            include 'views/cart_view.php'; 
            ?>
            <div class="mt-4">
                <a href="galeria_venta.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver a la Tienda</a>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el pie de página del dashboard
include 'includes/footer.php';
?>