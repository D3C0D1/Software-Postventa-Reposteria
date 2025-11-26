<?php 
$is_dashboard_page = true;
$page_title = '';
require_once 'includes/auth_check.php'; 
require_once 'config/database.php'; // Conexión a la base de datos

$conn = conectarDB(); // <--- AÑADIDO: Inicializar la conexión

// Consulta para obtener el número de ventas de hoy
$query_ventas_hoy = "SELECT COUNT(id) AS total FROM ventas WHERE DATE(fecha) = CURDATE()";
$result_ventas_hoy = $conn->query($query_ventas_hoy);
$ventas_hoy = $result_ventas_hoy->fetch_assoc()['total'];

// Consulta para obtener el número de ventas del mes actual
$query_ventas_mes = "SELECT COUNT(id) AS total FROM ventas WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
$result_ventas_mes = $conn->query($query_ventas_mes);
$ventas_mes = $result_ventas_mes->fetch_assoc()['total'];

// Consulta para obtener el número de productos (helados)
$query_productos = "SELECT COUNT(id) AS total FROM productos";
$result_productos = $conn->query($query_productos);
$total_productos = $result_productos->fetch_assoc()['total'];

// Consulta para obtener el número de subproductos (adiciones)
$query_subproductos = "SELECT COUNT(id) AS total FROM subproductos";
$result_subproductos = $conn->query($query_subproductos);
$total_subproductos = $result_subproductos->fetch_assoc()['total'];

include 'includes/header.php'; 
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mt-4">Bienvenido al Dashboard</h1>
            <p class="lead">
                Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></strong>. Tu rol es: <strong><?php echo htmlspecialchars($_SESSION['rol']); ?></strong>.
            </p>
        </div>
        <div class="text-end">
            <p class="mb-0">Kalua App © <?php echo date('Y'); ?></p>
        </div>
    </div>
    <hr>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Ventas de Hoy</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ventas_hoy; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ventas del Mes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ventas_mes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Helados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_productos; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ice-cream fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Adiciones</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_subproductos; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-candy-cane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Accesos Rápidos -->
    <div class="row mt-4">
        <div class="col-12">
            <h3 class="mb-3">Accesos Rápidos</h3>
        </div>

        <?php if (isAdmin()): ?>
            <!-- Accesos para Administrador -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="galeria_venta.php" class="shortcut-card">
                    <i class="fas fa-cash-register fa-3x"></i>
                    <span>Vender</span>
                </a>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="inventario.php" class="shortcut-card">
                    <i class="fas fa-boxes fa-3x"></i>
                    <span>Adiciones</span>
                </a>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="productos.php" class="shortcut-card">
                    <i class="fas fa-cookie-bite fa-3x"></i>
                    <span>Productos</span>
                </a>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="usuarios.php" class="shortcut-card">
                    <i class="fas fa-users fa-3x"></i>
                    <span>Usuarios</span>
                </a>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="ventas.php" class="shortcut-card">
                    <i class="fas fa-chart-bar fa-3x"></i>
                    <span>Reportes</span>
                </a>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="configuracion.php" class="shortcut-card">
                    <i class="fas fa-cog fa-3x"></i>
                    <span>Configuración</span>
                </a>
            </div>

        <?php elseif (isInvitado()): ?>
            <!-- Accesos para Invitado -->
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="galeria_venta.php" class="shortcut-card">
                    <i class="fas fa-cash-register fa-3x"></i>
                    <span>Vender</span>
                </a>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <a href="ventas.php" class="shortcut-card">
                    <i class="fas fa-chart-bar fa-3x"></i>
                    <span>Reportes</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <!-- Fin de Accesos Rápidos -->

    <div class="alert alert-info mt-4">
        <i class="fas fa-info-circle"></i>
        Selecciona una opción del menú lateral para comenzar a gestionar la aplicación.
    </div>
</div>

<?php include 'includes/footer.php'; ?>