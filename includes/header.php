<?php
// Iniciar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Obtener la configuración del sitio
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    // No mostrar error de conexión en producción
    $site_name = 'App'; // Nombre por defecto
    $logo_url = '';
    $color_primario = '#343a40';
    $color_secundario = '#ffffff';
    $color_texto = '#000000';
} else {
    $result = $conn->query("SELECT nombre_empresa, logo_url, color_primario, color_secundario, color_texto FROM configuracion WHERE id = 1");
    if ($result && $result->num_rows > 0) {
        $config = $result->fetch_assoc();
        $site_name = $config['nombre_empresa'];
        $logo_url = $config['logo_url'];
        $color_primario = $config['color_primario'];
        $color_secundario = $config['color_secundario'];
        $color_texto = $config['color_texto'];
    } else {
        $site_name = 'App'; // Nombre por defecto
        $logo_url = '';
        $color_primario = '#343a40';
        $color_secundario = '#ffffff';
        $color_texto = '#000000';
    }
    $conn->close();
}

// Comprobar si estamos en una página del dashboard
$is_dashboard_page = isset($is_dashboard_page) && $is_dashboard_page === true;

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' . htmlspecialchars($site_name) : htmlspecialchars($site_name); ?></title>
    <link rel="icon" href="<?php echo htmlspecialchars($logo_url); ?>" type="image/x-icon">
    <link rel="stylesheet" href="public/css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/all.min.css">
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="public/css/style.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

    <!-- Agregar estos estilos en la sección de estilos (alrededor de la línea 50) -->
    <style>
        .sidebar-nav ul li a {
            font-size: 1.1rem; /* Aumenta el tamaño de la fuente */
            color: <?php echo $color_texto; ?>; /* Color de texto principal */
            display: flex; /* Usar flexbox para alinear ícono y texto */
            align-items: center; /* Centrar verticalmente */
        }

        .sidebar-nav ul li a i {
            font-size: 1.4rem; /* Aumenta el tamaño del ícono */
            margin-right: 18px; /* Aumenta el espacio entre el ícono y el texto */
            width: 25px; /* Ancho fijo para el ícono para alineación */
            text-align: center; /* Centrar el ícono */
            color: <?php echo $color_primario; ?>; /* Color primario para los íconos */
        }

        .sidebar-nav ul li a:hover {
            background-color: <?php echo $color_primario; ?>; /* Color primario al pasar el mouse */
            color: <?php echo $color_secundario; ?>; /* Color secundario para el texto al pasar el mouse */
        }

        .sidebar-nav ul li a:hover i {
            color: <?php echo $color_secundario; ?>; /* Color secundario para los íconos al pasar el mouse */
        }

        @keyframes fa-beat {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        .btn-header-action.btn-logout i {
            animation: fa-beat 1s infinite;
        }

        .btn-header-action.btn-logout:hover i {
            animation: none;
        }

        @keyframes blinker {
            50% {
                opacity: 0.5;
            }
        }
        .btn-pdf-blink {
            animation: blinker 1s linear infinite;
        }
        
        /* Estilos para el botón de notificaciones */
        .notification-bell-container {
            position: relative;
            margin-right: 15px;
        }
        
        .notification-bell {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #555;
            cursor: pointer;
            position: relative;
            padding: 5px;
        }
        
        .notification-bell i {
            animation: bell-shake 4s ease-in-out infinite;
        }
        
        @keyframes bell-shake {
            0%, 50%, 100% {
                transform: rotate(0);
            }
            5%, 15%, 25%, 35%, 45% {
                transform: rotate(13deg);
            }
            10%, 20%, 30%, 40% {
                transform: rotate(-13deg);
            }
        }
        
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #ff3547;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 53, 71, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 53, 71, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 53, 71, 0);
            }
        }
        
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 300px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            overflow: hidden;
        }
        
        .notification-header {
            padding: 10px 15px;
            background-color: <?php echo $color_primario; ?>;
            color: <?php echo $color_secundario; ?>;
            border-bottom: 1px solid #eee;
        }
        
        .notification-header h6 {
            margin: 0;
            font-weight: 600;
        }
        
        .notification-body {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item p {
            margin: 0;
            font-size: 0.9rem;
        }
        
        .notification-loading {
            padding: 15px;
            text-align: center;
            color: #6c757d;
        }
        
        .notification-empty {
            padding: 15px;
            text-align: center;
            color: #6c757d;
        }

        /* Estilos para el modal de notificaciones */
        .notification-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1050;
            justify-content: center;
            align-items: center;
        }

        .notification-modal-content {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-modal-header {
            padding: 15px;
            background-color: #212529;
            color: #F8F9FA;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .notification-modal-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .notification-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #F8F9FA;
            cursor: pointer;
        }

        .notification-modal-body {
            padding: 0;
            max-height: 60vh;
            overflow-y: auto;
        }

        /* Estilos responsivos para móviles */
        @media (max-width: 576px) {
            .notification-modal-content {
                width: 95%;
                max-height: 70vh;
            }
            .notification-modal-body {
                max-height: 50vh;
            }
        }
    </style>

</head>
<body class="sb-nav-fixed <?php echo $is_dashboard_page ? 'sb-sidenav-toggled' : ''; ?>">

<?php if ($is_dashboard_page && isset($_SESSION['login']) && $_SESSION['login'] === true): ?>
    <div class="dashboard-layout">
        <aside class="sidebar">
                <div class="sidebar-header">
                    <a href="dashboard.php" class="sidebar-brand">
                        <?php echo htmlspecialchars($site_name); ?>
                    </a>
                </div>
                <nav class="sidebar-nav">
                    <ul>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
                        
                        <?php if (isAdmin()): ?>
                            <li><a href="galeria_venta.php"><i class="fas fa-cash-register"></i><span>Vender</span></a></li>
                            <li><a href="inventario.php"><i class="fas fa-boxes"></i><span>Adiciones</span></a></li>
                            <li><a href="productos.php"><i class="fas fa-cookie-bite"></i><span>Productos</span></a></li>
                            <li><a href="usuarios.php"><i class="fas fa-users"></i><span>Usuarios</span></a></li>
                            <li><a href="ventas.php"><i class="fas fa-chart-bar"></i><span>Reportes</span></a></li>
                            <li><a href="configuracion.php"><i class="fas fa-cog"></i><span>Configuración</span></a></li>
                            <?php if (isOperador()): ?>
                                <li><a href="error_logs.php"><i class="fas fa-exclamation-triangle"></i><span>Registro de Errores</span></a></li>
                            <?php endif; ?>
                        <?php elseif (isInvitado()): ?>
                            <li><a href="galeria_venta.php"><i class="fas fa-cash-register"></i><span>Vender</span></a></li>
                            <li><a href="ventas.php"><i class="fas fa-chart-bar"></i><span>Reportes</span></a></li>
                        <?php else: ?>
                            <li><a href="galeria_venta.php"><i class="fas fa-store"></i><span>Tienda</span></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <div class="sidebar-footer">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
                        <small class="user-role"><?php echo htmlspecialchars($_SESSION['rol']); ?></small>
                    </div>
                    <a href="logout.php" class="logout-link" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </aside>

            <div class="main-wrapper">
                <!-- Modificar la sección del header (alrededor de la línea 270) -->
                <header class="top-header">
                    <h1 class="page-title"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h1>
                    
                    <!-- Botón de notificaciones -->
                    <div class="notification-bell-container">
                        <button id="notificationBell" class="notification-bell" title="Notificaciones">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notificationCount">0</span>
                        </button>
                    </div>
                    
                    <?php 
                    // Determina la página actual
                    $current_page = basename($_SERVER['PHP_SELF']);

                    if ($current_page == 'dashboard.php') { ?>
                        <a href="logout.php" class="btn-header-action btn-logout" title="Cerrar Sesión">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    <?php } elseif ($current_page == 'galeria_venta.php') { ?>
                        <a href="dashboard.php" class="btn-header-action btn-back" title="Volver al Dashboard">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    <?php } else { ?>
                        <a href="javascript:history.back()" class="btn-header-action btn-back" title="Volver">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    <?php } ?>
                </header>

                <main class="main-content">
        <?php else: ?>
            <main>
        <?php endif; ?>
<!-- Agregar este código justo antes de </body> -->
<!-- Modal de Notificaciones -->
<div class="notification-modal" id="notificationModal">
    <div class="notification-modal-content">
        <div class="notification-modal-header">
            <h5>Notificaciones</h5>
            <button class="notification-modal-close" id="closeNotificationModal">&times;</button>
        </div>
        <div class="notification-modal-body" id="notificationList">
            <div class="notification-loading">Cargando...</div>
        </div>
    </div>
</div>

<style>
    /* Animación para el nombre de la empresa */
    .sidebar-brand {
        font-size: 2.2rem; /* Tamaño más grande */
        font-weight: bold;
        color: <?php echo $color_primario; ?>;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center; /* Centrar horizontalmente */
        animation: textPulse 1.5s infinite; /* Parpadeo más rápido */
        width: 100%; /* Asegurar que ocupe todo el ancho disponible */
        padding: 15px 0; /* Añadir espacio arriba y abajo */
        text-shadow: 0 0 5px rgba(255, 255, 255, 0.5); /* Efecto de brillo */
    }
    
    @keyframes textPulse {
        0% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.1); } /* Mayor cambio de escala y opacidad */
        100% { opacity: 1; transform: scale(1); }
    }
    
    /* Estilo para el sidebar con colores claros */
    .sidebar {
        background-color: #f5e6d8; /* Café claro */
        border-right: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }
    
    /* Estilo para el encabezado del sidebar */
    .sidebar-header {
        text-align: center;
        padding: 15px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        background-color: rgba(255, 255, 255, 0.3); /* Fondo ligeramente más claro */
    }
    
    /* Animaciones para los botones del menú */
    .sidebar-nav ul li a {
        font-size: 1.1rem;
        color: #6b4f3e; /* Color café medio para el texto */
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        padding: 12px 15px;
        margin: 5px 10px;
        border-radius: 8px;
        background-color: #e8d5c4; /* Color café equilibrado para los botones */
        border: 1px solid #d9c0a9; /* Borde ligeramente más oscuro */
    }
    
    .sidebar-nav ul li a:before {
        content: "";
        position: absolute;
        left: -100%;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
    }
    
    .sidebar-nav ul li a:hover:before {
        left: 0;
    }
    
    .sidebar-nav ul li a:hover {
        background-color: #d9c0a9; /* Color café ligeramente más oscuro al pasar el mouse */
        color: #4a3728; /* Color de texto más oscuro al pasar el mouse */
    }
    
    .sidebar-nav ul li a i {
        font-size: 1.4rem;
        margin-right: 18px;
        width: 25px;
        text-align: center;
        color: #8c6e5a; /* Color café más oscuro para los íconos */
        transition: transform 0.3s ease;
    }
    
    .sidebar-nav ul li a:hover i {
        transform: scale(1.2);
        color: #6b4f3e; /* Color café medio para los íconos al pasar el mouse */
    }
    
    /* Botón de cerrar sesión rojo y redondo */
    .logout-link, .btn-header-action.btn-logout {
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .logout-link:hover, .btn-header-action.btn-logout:hover {
        transform: scale(1.1);
        background-color: #c82333;
    }
    
    .logout-link i, .btn-header-action.btn-logout i {
        font-size: 1.2rem;
    }
    
    /* Estilo para el footer del sidebar */
    .sidebar-footer {
        background-color: #e8d5c4; /* Color café equilibrado */
        border-top: 1px solid #d9c0a9; /* Borde ligeramente más oscuro */
        padding: 15px;
    }
    
    /* Ajustes para el usuario en el footer */
    .user-info {
        color: #6b4f3e; /* Color café medio para el texto */
    }
</style>
<!-- Eliminar todas estas etiquetas div de cierre innecesarias -->

<script src="public/js/bootstrap/bootstrap.bundle.min.js"></script>

<style>
    /* Estilos adicionales para evitar desbordamientos */
    body {
        overflow-x: hidden;
    }
    
    .main-wrapper {
        width: 100%;
        overflow-x: hidden;
    }
    
    .main-content {
        padding: 20px;
        overflow-x: hidden;
    }
    
    /* Ajustes responsivos para móviles */
    @media (max-width: 768px) {
        .top-header {
            flex-wrap: wrap;
            justify-content: center;
            padding: 10px;
        }
        
        .page-title {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .notification-bell-container {
            margin-right: 10px;
        }
    }
</style>
</body>
</html>
