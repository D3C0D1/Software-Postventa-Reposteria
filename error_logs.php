<?php
$is_dashboard_page = true;
$page_title = 'Registro de Errores';
require_once 'includes/auth_check.php';
require_once 'config/database.php';

// Proteger esta página solo para administradores y operadores
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

// Solo permitir acceso a operadores y administradores
if (!($_SESSION['rol'] === 'operador' || $_SESSION['rol'] === 'admin')) {
    header('Location: dashboard.php');
    exit;
}

$db = conectarDB();

// Paginación
$registros_por_pagina = 20;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Filtros
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Construir consulta
$query = "SELECT e.*, u.nombre as nombre_usuario 
          FROM error_logs e 
          LEFT JOIN usuarios u ON e.usuario_id = u.id";

$condiciones = [];
$params = [];
$types = '';

if (!empty($filtro_tipo)) {
    $condiciones[] = "e.tipo = ?";
    $params[] = $filtro_tipo;
    $types .= 's';
}

if (!empty($filtro_fecha)) {
    $condiciones[] = "DATE(e.fecha) = ?";
    $params[] = $filtro_fecha;
    $types .= 's';
}

if (!empty($condiciones)) {
    $query .= " WHERE " . implode(' AND ', $condiciones);
}

$query .= " ORDER BY e.fecha DESC LIMIT ?, ?";
$params[] = $inicio;
$params[] = $registros_por_pagina;
$types .= 'ii';

// Ejecutar consulta
$stmt = $db->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();
$errores = $resultado->fetch_all(MYSQLI_ASSOC);

// Contar total de registros para paginación
$query_total = "SELECT COUNT(*) as total FROM error_logs";

if (!empty($condiciones)) {
    $query_total .= " WHERE " . implode(' AND ', $condiciones);
}

$stmt_total = $db->prepare($query_total);

if (!empty($params) && count($params) > 2) {
    // Eliminar los últimos dos parámetros (inicio y límite)
    $params_count = array_slice($params, 0, -2);
    $types_count = substr($types, 0, -2);
    $stmt_total->bind_param($types_count, ...$params_count);
}

$stmt_total->execute();
$resultado_total = $stmt_total->get_result()->fetch_assoc();
$total_registros = $resultado_total['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener tipos de errores para el filtro
$query_tipos = "SELECT DISTINCT tipo FROM error_logs ORDER BY tipo";
$resultado_tipos = $db->query($query_tipos);
$tipos_error = $resultado_tipos->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Registro de Errores y Depuración</h1>
    <p class="lead">Visualiza y gestiona los errores registrados en el sistema.</p>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i>
            Filtros
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="tipo" class="form-label">Tipo de Error</label>
                    <select name="tipo" id="tipo" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($tipos_error as $tipo): ?>
                            <option value="<?php echo htmlspecialchars($tipo['tipo']); ?>" <?php echo $filtro_tipo === $tipo['tipo'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tipo['tipo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo htmlspecialchars($filtro_fecha); ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="error_logs.php" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Errores -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-exclamation-triangle"></i>
            Listado de Errores
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Mensaje</th>
                            <th>Archivo</th>
                            <th>Línea</th>
                            <th>IP</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($errores)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay errores registrados</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($errores as $error): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($error['fecha']))); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo match($error['tipo']) {
                                                'ERROR' => 'bg-danger',
                                                'WARNING' => 'bg-warning text-dark',
                                                'NOTICE' => 'bg-info text-dark',
                                                default => 'bg-secondary'
                                            }; 
                                        ?>">
                                            <?php echo htmlspecialchars($error['tipo']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($error['mensaje']); ?></td>
                                    <td><?php echo htmlspecialchars($error['archivo']); ?></td>
                                    <td><?php echo htmlspecialchars($error['linea']); ?></td>
                                    <td><?php echo htmlspecialchars($error['ip']); ?></td>
                                    <td><?php echo htmlspecialchars($error['nombre_usuario'] ?? 'N/A'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info ver-detalle" data-id="<?php echo $error['id']; ?>">Ver Detalle</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <nav aria-label="Paginación de errores">
                    <ul class="pagination justify-content-center mt-4">
                        <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>&tipo=<?php echo urlencode($filtro_tipo); ?>&fecha=<?php echo urlencode($filtro_fecha); ?>">
                                Anterior
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?php echo $pagina_actual == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $i; ?>&tipo=<?php echo urlencode($filtro_tipo); ?>&fecha=<?php echo urlencode($filtro_fecha); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>&tipo=<?php echo urlencode($filtro_tipo); ?>&fecha=<?php echo urlencode($filtro_fecha); ?>">
                                Siguiente
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Detalle -->
<div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleModalLabel">Detalle del Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detalleModalBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar clic en botón de ver detalle
        const botonesDetalle = document.querySelectorAll('.ver-detalle');
        botonesDetalle.forEach(boton => {
            boton.addEventListener('click', function() {
                const errorId = this.getAttribute('data-id');
                const modal = new bootstrap.Modal(document.getElementById('detalleModal'));
                modal.show();
                
                // Aquí se podría implementar una llamada AJAX para cargar los detalles
                // Por ahora, solo mostramos un mensaje
                document.getElementById('detalleModalBody').innerHTML = `
                    <div class="alert alert-info">
                        Detalles completos del error ID: ${errorId}
                    </div>
                    <pre class="bg-light p-3">Información detallada del error...</pre>
                `;
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>