<?php
$is_dashboard_page = true;
$page_title = '';
require_once 'includes/auth_check.php'; // session_start() está aquí

// Incluir el encabezado del dashboard
include 'includes/header.php';
?>

<style>
.cart-sidebar {
    position: -webkit-sticky; /* Para Safari */
    position: sticky;
    top: 80px; /* Ajusta este valor según la altura de tu cabecera */
    height: calc(100vh - 80px);
    overflow-y: auto;
}

/* Estilo para el botón de PDF parpadeante */
@keyframes parpadeo {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.btn-pdf {
    background-color: #dc3545 !important;
    color: white !important;
    animation: parpadeo 1.5s infinite;
    font-weight: bold;
}

/* Estilos mejorados para el botón flotante del carrito */
.cart-fab {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 70px; /* Más grande */
    height: 70px; /* Más grande */
    background-color: <?php echo $color_secundario ?? '#F55D93'; ?>;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem; /* Más grande */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    text-decoration: none;
    z-index: 1050;
    transition: all 0.3s ease;
    animation: pulse 2s infinite, bounce 3s infinite alternate;
}

.cart-fab:hover {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
    background-color: <?php echo $color_primario ?? '#6D3D2A'; ?>;
}

.cart-item-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: <?php echo $color_primario ?? '#6D3D2A'; ?>;
    color: white;
    border-radius: 50%;
    padding: 0.4rem 0.8rem;
    font-size: 1rem;
    font-weight: 700;
    border: 3px solid white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    animation: bounce 1.5s infinite alternate;
}

/* Animaciones para el botón del carrito */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Media query para tablets */
@media (max-width: 992px) {
    .cart-column {
        display: none; /* Ocultar la columna del carrito en tablets */
    }
    .gallery-column {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .cart-fab {
        display: flex; /* Asegurar que el botón flotante sea visible */
    }
    
    /* Reducción de tamaño de texto para tarjetas en tablets */
    .product-card .card-title {
        font-size: 0.95rem;
        line-height: 1.3;
        margin-bottom: 0.5rem;
    }
    
    .product-card .card-text {
        font-size: 1rem !important;
    }
    
    .product-card .btn {
        font-size: 0.85rem;
        padding: 0.375rem 0.5rem;
    }
    
    .product-card .input-group input {
        font-size: 0.85rem;
        padding: 0.375rem 0.5rem;
    }
}

/* Media query específico para resolución 770x390 */
@media (max-width: 770px) and (max-height: 390px) {
    /* Reducción significativa del tamaño de texto para esta resolución específica */
    .product-card .card-title {
        font-size: 0.75rem;
        line-height: 1.1;
        margin-bottom: 0.3rem;
        height: 1.65rem; /* Altura fija para evitar desbordamiento */
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    
    .product-card .card-text {
        font-size: 0.8rem !important;
        margin-bottom: 0.3rem;
    }
    
    .product-card .btn {
        font-size: 0.7rem;
        padding: 0.2rem 0.3rem;
    }
    
    .product-card .input-group input {
        font-size: 0.7rem;
        padding: 0.2rem 0.3rem;
        height: auto;
    }
    
    /* Reducir el padding interno de las tarjetas */
    .product-card .card-body {
        padding: 0.5rem;
    }
    
    /* Reducir altura de las imágenes */
    .product-image {
        height: 100px;
        padding: 5px;
    }
    
    /* Ajustar el grid para mostrar más productos por fila */
    #galeriaProductos, #galeriaSubproductos {
        --bs-gutter-x: 0.5rem;
        --bs-gutter-y: 0.5rem;
    }
    
    .row-cols-sm-2 > * {
        flex: 0 0 auto;
        width: 33.333333%;
    }
}

/* Media query para móviles */
@media (max-width: 576px) {
    /* Reducción adicional de tamaño de texto para tarjetas en móviles */
    .product-card .card-title {
        font-size: 0.85rem;
        line-height: 1.2;
        margin-bottom: 0.4rem;
    }
    
    .product-card .card-text {
        font-size: 0.9rem !important;
        margin-bottom: 0.4rem;
    }
    
    .product-card .btn {
        font-size: 0.8rem;
        padding: 0.25rem 0.4rem;
    }
    
    .product-card .input-group input {
        font-size: 0.8rem;
        padding: 0.25rem 0.4rem;
    }
    
    /* Ajustar el espaciado interno de las tarjetas */
    .product-card .card-body {
        padding: 0.75rem;
    }
    
    /* Reducir altura de las imágenes en móvil */
    .product-image {
        height: 120px;
    }
}
</style>

<div class="container-fluid mt-4">

    <!-- Botón de Carrito Flotante (mejorado y siempre visible en tablets) -->
    <a href="carrito.php" class="cart-fab">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-item-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
    </a>

    <div class="row">
        <!-- Columna de la Galería de Productos -->
        <div class="col-lg-8 gallery-column">
            <?php 
            // Mostrar mensajes de éxito o error del carrito
            if (isset($_SESSION['success_message'])) {
                echo "<div class='alert alert-success d-flex justify-content-between align-items-center'>";
                echo $_SESSION['success_message'];
                if (isset($_GET['venta_id'])) {
                    echo "<a href='generar_factura.php?id=" . htmlspecialchars($_GET['venta_id']) . "' target='_blank' class='btn btn-pdf btn-sm'><i class='fas fa-file-pdf'></i> Ver PDF</a>";
                }
                echo "</div>";
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
                unset($_SESSION['error_message']);
            }

            // Incluir el contenido de la galería de productos
            include 'views/product_gallery.php'; 
            ?>
        </div>

        <!-- Columna del Carrito de Compras (visible solo en desktop) -->
        <div class="col-lg-4 cart-column">
            <div class="cart-sidebar">
                <?php include 'views/cart_view.php'; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el pie de página del dashboard
include 'includes/footer.php';
?>