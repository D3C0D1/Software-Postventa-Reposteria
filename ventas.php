<?php
// Se elimina session_start(); de aquí porque ya se llama en auth_check.php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

$conexion = conectarDB();

// Procesar formulario de movimiento de caja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_movement') {
    $tipo = $_POST['tipo'];
    $monto = $_POST['monto'];
    $descripcion = $_POST['descripcion'];
    
    // Asegurarse de que el user_id de la sesión está disponible
    if (isset($_SESSION['user_id'])) {
        $usuario_id = $_SESSION['user_id'];

        $stmt = mysqli_prepare($conexion, "INSERT INTO movimientos_caja (tipo, monto, descripcion, usuario_id) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sdsi", $tipo, $monto, $descripcion, $usuario_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: ventas.php?status=success");
            exit();
        } else {
            $error_message = "Error al registrar el movimiento: " . mysqli_error($conexion);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Error: Sesión de usuario no válida. Por favor, inicie sesión de nuevo.";
    }
}

// Procesar la eliminación de una venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_sale') {
    $venta_id = $_POST['sale_id'];

    // Opcional: Iniciar una transacción para asegurar la integridad de los datos
    mysqli_begin_transaction($conexion);

    try {
        // Eliminar los detalles de la venta
        $stmt_detalles = mysqli_prepare($conexion, "DELETE FROM venta_detalles WHERE venta_id = ?");
        mysqli_stmt_bind_param($stmt_detalles, "i", $venta_id);
        mysqli_stmt_execute($stmt_detalles);
        mysqli_stmt_close($stmt_detalles);

        // Eliminar la venta principal
        $stmt_venta = mysqli_prepare($conexion, "DELETE FROM ventas WHERE id = ?");
        mysqli_stmt_bind_param($stmt_venta, "i", $venta_id);
        mysqli_stmt_execute($stmt_venta);
        mysqli_stmt_close($stmt_venta);

        // Si todo fue bien, confirmar la transacción
        mysqli_commit($conexion);

        header("Location: ventas.php?status=sale_deleted");
        exit();
    } catch (mysqli_sql_exception $exception) {
        // Si algo falló, revertir la transacción
        mysqli_rollback($conexion);
        $error_message = "Error al eliminar la venta: " . $exception->getMessage();
    }
}

// Procesar la eliminación de un movimiento de caja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_movement') {
    $movement_id = $_POST['movement_id'];
    $stmt = mysqli_prepare($conexion, "DELETE FROM movimientos_caja WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $movement_id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ventas.php?status=deleted");
        exit();
    } else {
        $error_message = "Error al eliminar el movimiento: " . mysqli_error($conexion);
    }
    mysqli_stmt_close($stmt);
}

$is_dashboard_page = true;
$page_title = 'Historial y Caja'; // Añadido para claridad

require_once 'includes/header.php';

// Se elimina la conexión a la DB de aquí porque se movió arriba

// Se elimina el procesamiento de formularios de aquí porque se movió arriba

// Se elimina la variable $hoy = date('Y-m-d'); ya que usaremos CURDATE() de MySQL.

// Consulta para el total de ventas de hoy
$query_ventas_hoy = "SELECT SUM(total) as total FROM ventas WHERE DATE(fecha) = CURDATE()";
$resultado_ventas_hoy = mysqli_query($conexion, $query_ventas_hoy);
$total_ventas_hoy = mysqli_fetch_assoc($resultado_ventas_hoy)['total'] ?? 0;

// Consulta para los movimientos de caja de hoy
$query_movimientos_hoy = "SELECT SUM(IF(tipo='entrada', monto, -monto)) as total FROM movimientos_caja WHERE DATE(fecha) = CURDATE()";
$resultado_movimientos_hoy = mysqli_query($conexion, $query_movimientos_hoy);
$total_movimientos_hoy = mysqli_fetch_assoc($resultado_movimientos_hoy)['total'] ?? 0;

$total_hoy = $total_ventas_hoy + $total_movimientos_hoy;

// Consulta para el total acumulado de ventas
$query_ventas_acumulado = "SELECT SUM(total) as total FROM ventas";
$resultado_ventas_acumulado = mysqli_query($conexion, $query_ventas_acumulado);
$total_ventas_acumulado = mysqli_fetch_assoc($resultado_ventas_acumulado)['total'] ?? 0;

// Consulta para el total acumulado de movimientos de caja
$query_movimientos_acumulado = "SELECT SUM(IF(tipo='entrada', monto, -monto)) as total FROM movimientos_caja";
$resultado_movimientos_acumulado = mysqli_query($conexion, $query_movimientos_acumulado);
$total_movimientos_acumulado = mysqli_fetch_assoc($resultado_movimientos_acumulado)['total'] ?? 0;

$total_acumulado = $total_ventas_acumulado + $total_movimientos_acumulado;

// Obtener todos los clientes y usuarios para los filtros
$clientes = [];
$query_clientes = "SELECT DISTINCT nombre_cliente FROM ventas WHERE nombre_cliente IS NOT NULL AND nombre_cliente != '' ORDER BY nombre_cliente ASC";
$resultado_clientes = mysqli_query($conexion, $query_clientes);
while ($row = mysqli_fetch_assoc($resultado_clientes)) {
    $clientes[] = $row['nombre_cliente'];
}

$todos_usuarios = [];
$query_todos_usuarios = "SELECT id, nombre FROM usuarios ORDER BY nombre ASC";
$resultado_todos_usuarios = mysqli_query($conexion, $query_todos_usuarios);
while ($row = mysqli_fetch_assoc($resultado_todos_usuarios)) {
    $todos_usuarios[] = $row;
}


// --- Lógica de Filtrado y Paginación ---

// Valores por defecto
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? '';
$filtro_fecha_fin = $_GET['fecha_fin'] ?? '';
$filtro_notas = $_GET['notas'] ?? '';
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_usuario = $_GET['usuario_id'] ?? ''; // Añadir esta línea
$registros_por_pagina = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Construcción de la consulta con filtros
$where_clauses = [];
$params = [];
$types = '';

if ($filtro_tipo !== '') {
    if ($filtro_tipo === 'venta') {
        $where_clauses[] = "tipo_registro = 'venta'";
    } else {
        $where_clauses[] = "tipo_registro = ?";
        $params[] = $filtro_tipo;
        $types .= 's';
    }
}

if ($filtro_fecha_inicio !== '') {
    $where_clauses[] = "DATE(fecha) >= ?";
    $params[] = $filtro_fecha_inicio;
    $types .= 's';
}

if ($filtro_fecha_fin !== '') {
    $where_clauses[] = "DATE(fecha) <= ?";
    $params[] = $filtro_fecha_fin;
    $types .= 's';
}

if ($filtro_notas !== '') {
    $where_clauses[] = "(descripcion LIKE ? OR nombre_cliente LIKE ?)";
    $like_notas = "%{$filtro_notas}%";
    $params[] = $like_notas;
    $params[] = $like_notas;
    $types .= 'ss';
}

$base_query = "
    SELECT 'venta' as tipo_registro, id, fecha, total, detalle_venta as descripcion, usuario_id, nombre_cliente
    FROM ventas
    UNION ALL
    SELECT tipo as tipo_registro, id, fecha, IF(tipo='entrada', monto, -monto) as total, descripcion, usuario_id, 'N/A' as nombre_cliente
    FROM movimientos_caja
";

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
}

// Consulta para contar el total de registros con filtros
$query_total_registros = "SELECT COUNT(*) as total FROM ({$base_query}) as combined_records {$where_sql}";
$stmt_total = mysqli_prepare($conexion, $query_total_registros);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_total, $types, ...$params);
}
mysqli_stmt_execute($stmt_total);
$total_registros = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total))['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consulta para obtener los registros paginados y filtrados
$query_combinada = "SELECT * FROM ({$base_query}) as combined_records {$where_sql} ORDER BY fecha DESC LIMIT ? OFFSET ?";
$stmt_combinada = mysqli_prepare($conexion, $query_combinada);

$all_params = $params;
$all_params[] = $registros_por_pagina;
$all_params[] = $offset;
$all_types = $types . 'ii';

if (!empty($all_params)) {
    mysqli_stmt_bind_param($stmt_combinada, $all_types, ...$all_params);
}

mysqli_stmt_execute($stmt_combinada);
$resultado_combinado = mysqli_stmt_get_result($stmt_combinada);
$registros = mysqli_fetch_all($resultado_combinado, MYSQLI_ASSOC);

// Obtener nombres de usuario para los registros
$usuarios = [];
$query_usuarios = "SELECT id, nombre FROM usuarios";
$resultado_usuarios = mysqli_query($conexion, $query_usuarios);
while ($usuario = mysqli_fetch_assoc($resultado_usuarios)) {
    $usuarios[$usuario['id']] = $usuario['nombre'];
}

?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Historial y Caja</h1>
    <ol class="breadcrumb mb-4 bg-light p-3 rounded-3">
        <li class="breadcrumb-item active">Aquí puedes ver y gestionar el historial de ventas y los movimientos de caja.</li>
    </ol>

    <!-- Resumen de Ventas -->
    <div class="row">
        <div class="col-xl-6 col-md-6">
            <div class="card summary-card bg-success text-white mb-4">
                <div class="card-body">Ventas de Hoy</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="h4 text-white">$<?php echo number_format($total_hoy, 2); ?></span>
                    <i class="fas fa-calendar-day fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6">
            <div class="card summary-card bg-info text-white mb-4">
                <div class="card-body">Ventas Acumuladas</div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="h4 text-white">$<?php echo number_format($total_acumulado, 2); ?></span>
                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Movimientos de Caja -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-cash-register me-1"></i>
            Registrar Movimiento de Caja
        </div>
        <div class="card-body">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div class="alert alert-success">Movimiento registrado con éxito.</div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
                <div class="alert alert-info">Movimiento eliminado correctamente.</div>
            <?php endif; ?>
            <form id="movementForm" method="POST" action="ventas.php">
                <input type="hidden" name="action" value="add_movement">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="tipo" class="form-label">Tipo de Movimiento</label>
                        <select id="tipo" name="tipo" class="form-select" required>
                            <option value="entrada">Entrada de Dinero</option>
                            <option value="salida">Salida de Dinero (Gasto)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="monto" class="form-label">Monto</label>
                        <input type="number" class="form-control" id="monto" name="monto" step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label for="descripcion" class="form-label">Notas / Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                    </div>
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-success">Registrar Movimiento</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Formulario de Búsqueda -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-search me-1"></i>
            Filtrar Historial
        </div>
        <div class="card-body">
            <form id="filterForm" method="GET" action="ventas.php">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="tipo_filtro" class="form-label">Tipo de Registro</label>
                        <select id="tipo_filtro" name="tipo" class="form-select">
                            <option value="">Todos</option>
                            <option value="venta" <?php echo ($filtro_tipo === 'venta') ? 'selected' : ''; ?>>Venta de Cliente</option>
                            <option value="entrada" <?php echo ($filtro_tipo === 'entrada') ? 'selected' : ''; ?>>Entrada de Caja</option>
                            <option value="salida" <?php echo ($filtro_tipo === 'salida') ? 'selected' : ''; ?>>Salida de Caja</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($filtro_fecha_inicio); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($filtro_fecha_fin); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="notas" class="form-label">Buscar en Notas</label>
                        <input type="text" class="form-control" id="notas" name="notas" placeholder="Cliente, descripción..." value="<?php echo htmlspecialchars($filtro_notas); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="filtro_cliente" class="form-label">Cliente</label>
                        <select id="filtro_cliente" name="cliente" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo htmlspecialchars($cliente); ?>" <?php echo ($filtro_cliente === $cliente) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cliente); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtro_usuario" class="form-label">Usuario</label>
                        <select id="filtro_usuario" name="usuario_id" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($todos_usuarios as $usuario): ?>
                                <option value="<?php echo htmlspecialchars($usuario['id']); ?>" <?php echo ($filtro_usuario == $usuario['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($usuario['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="limit" class="form-label">Mostrar</label>
                        <select id="limit" name="limit" class="form-select">
                            <option value="5" <?php echo ($registros_por_pagina == 5) ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo ($registros_por_pagina == 10) ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo ($registros_por_pagina == 20) ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo ($registros_por_pagina == 50) ? 'selected' : ''; ?>>50</option>
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <a href="ventas.php" class="btn btn-secondary w-100">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Historial -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Historial de Ventas y Movimientos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Cliente / Descripción</th>
                            <th>Notas Adicionales</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="salesHistory">
                        <?php if (empty($registros)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No se encontraron registros con los filtros aplicados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registros as $registro): ?>
                                <tr class="<?php echo $registro['tipo_registro'] === 'entrada' ? 'table-success' : ($registro['tipo_registro'] === 'salida' ? 'table-danger' : ''); ?>">
                                    <td><?php echo htmlspecialchars($registro['id']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($registro['fecha']))); ?></td>
                                    <td>
                                        <?php 
                                            if ($registro['tipo_registro'] === 'venta') echo '<span class="badge bg-primary">Venta</span>';
                                            elseif ($registro['tipo_registro'] === 'entrada') echo '<span class="badge bg-success">Entrada</span>';
                                            else echo '<span class="badge bg-danger">Salida</span>';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($registro['nombre_cliente'] !== 'N/A' ? $registro['nombre_cliente'] : $registro['descripcion']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['nombre_cliente'] !== 'N/A' ? $registro['descripcion'] : '-'); ?></td>
                                    <td>$<?php echo number_format($registro['total'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($usuarios[$registro['usuario_id']] ?? 'Desconocido'); ?></td>
                                    <td>
                                        <?php if ($registro['tipo_registro'] === 'venta'): ?>
                                            <a href="generar_pdf_venta.php?id=<?php echo $registro['id']; ?>" class="btn btn-danger btn-sm" target="_blank" title="Ver PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <?php if (!isInvitado()): ?>
                                            <form action="ventas.php" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta venta?');">
                                                <input type="hidden" name="action" value="delete_sale">
                                                <input type="hidden" name="sale_id" value="<?php echo $registro['id']; ?>">
                                                <button type="submit" class="btn btn-warning btn-sm" title="Eliminar Venta">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php if (!isInvitado()): ?>
                                            <form action="ventas.php" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este movimiento?');">
                                                <input type="hidden" name="action" value="delete_movement">
                                                <input type="hidden" name="movement_id" value="<?php echo $registro['id']; ?>">
                                                <button type="submit" class="btn btn-warning btn-sm" title="Eliminar Movimiento">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($pagina_actual > 1): ?>
                        <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagina_actual - 1])); ?>">Anterior</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                        <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagina_actual + 1])); ?>">Siguiente</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    const salesHistory = document.getElementById('salesHistory');
    const paginationNav = document.getElementById('paginationNav');
    let currentPage = 1;

    // No AJAX en el formulario de movimiento, se enviará de forma tradicional
    // const movementForm = document.getElementById('movementForm');
    // movementForm.addEventListener('submit', function(e) {
    //     e.preventDefault();
    //     const formData = new FormData(movementForm);
    //     fetch('ventas.php', {
    //         method: 'POST',
    //         body: formData
    //     }).then(() => {
    //         fetchResults(); // Recargar la tabla
    //         movementForm.reset();
    //     });
    // });

    function fetchResults() {
        const formData = new FormData(filterForm);
        formData.append('page', currentPage);
        const params = new URLSearchParams(formData).toString();

        fetch(`ajax_filter_sales.php?${params}`)
            .then(response => response.json())
            .then(data => {
                salesHistory.innerHTML = data.tableContent;
                paginationNav.innerHTML = data.pagination;
            })
            .catch(error => console.error('Error:', error));
    }

    // Event listeners para los filtros
    document.getElementById('notas').addEventListener('keyup', () => { currentPage = 1; fetchResults(); });
    document.getElementById('filtro_cliente').addEventListener('change', () => { currentPage = 1; fetchResults(); });
    document.getElementById('filtro_usuario').addEventListener('change', () => { currentPage = 1; fetchResults(); });
    document.getElementById('tipo_filtro').addEventListener('change', () => { currentPage = 1; fetchResults(); });
    document.getElementById('fecha_inicio').addEventListener('change', () => { currentPage = 1; fetchResults(); });
    document.getElementById('fecha_fin').addEventListener('change', () => { currentPage = 1; fetchResults(); });
    document.getElementById('limit').addEventListener('change', () => { currentPage = 1; fetchResults(); });

    // Event listener para la paginación
    paginationNav.addEventListener('click', (e) => {
        if (e.target.tagName === 'A' && e.target.dataset.page) {
            e.preventDefault();
            currentPage = e.target.dataset.page;
            fetchResults();
        }
    });

    // Carga inicial
    fetchResults();
});
</script>

<?php
require_once 'includes/footer.php';
?>