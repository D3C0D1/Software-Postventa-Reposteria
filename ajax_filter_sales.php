<?php
session_start();
require_once 'config/database.php';

$conexion = conectarDB();

// --- Lógica de Filtrado y Paginación ---
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? '';
$filtro_fecha_fin = $_GET['fecha_fin'] ?? '';
$filtro_notas = $_GET['notas'] ?? '';
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_usuario = $_GET['usuario_id'] ?? '';
$registros_por_pagina = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

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

if ($filtro_cliente !== '') {
    $where_clauses[] = "nombre_cliente = ?";
    $params[] = $filtro_cliente;
    $types .= 's';
}

if ($filtro_usuario !== '') {
    $where_clauses[] = "usuario_id = ?";
    $params[] = $filtro_usuario;
    $types .= 'i';
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

$query_total_registros = "SELECT COUNT(*) as total FROM ({$base_query}) as combined_records {$where_sql}";
$stmt_total = mysqli_prepare($conexion, $query_total_registros);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_total, $types, ...$params);
}
mysqli_stmt_execute($stmt_total);
$total_registros = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total))['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

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

$usuarios = [];
$query_usuarios = "SELECT id, nombre FROM usuarios";
$resultado_usuarios = mysqli_query($conexion, $query_usuarios);
while ($usuario = mysqli_fetch_assoc($resultado_usuarios)) {
    $usuarios[$usuario['id']] = $usuario['nombre'];
}

// Generar contenido de la tabla
$tableContent = '';
if (empty($registros)) {
    $tableContent = '<tr><td colspan="8" class="text-center">No se encontraron registros.</td></tr>';
} else {
    foreach ($registros as $registro) {
        $row_class = $registro['tipo_registro'] === 'entrada' ? 'table-success' : ($registro['tipo_registro'] === 'salida' ? 'table-danger' : '');
        $tipo_badge = '';
        if ($registro['tipo_registro'] === 'venta') $tipo_badge = '<span class="badge bg-primary">Venta</span>';
        elseif ($registro['tipo_registro'] === 'entrada') $tipo_badge = '<span class="badge bg-success">Entrada</span>';
        else $tipo_badge = '<span class="badge bg-danger">Salida</span>';

        $cliente_desc = htmlspecialchars($registro['nombre_cliente'] !== 'N/A' ? $registro['nombre_cliente'] : $registro['descripcion']);
        $notas_adicionales = htmlspecialchars($registro['nombre_cliente'] !== 'N/A' ? $registro['descripcion'] : '-');
        $monto = '$' . number_format($registro['total'], 2);
        $usuario_nombre = htmlspecialchars($usuarios[$registro['usuario_id']] ?? 'Desconocido');

        $acciones = '';
        if ($registro['tipo_registro'] === 'venta') {
            $acciones = '<a href="generar_pdf_venta.php?id=' . $registro['id'] . '" class="btn btn-danger btn-sm" target="_blank" title="Ver PDF"><i class="fas fa-file-pdf"></i></a>';
        } else {
            // Acciones para movimientos de caja (si las hubiera)
        }

        $tableContent .= "<tr class='{$row_class}'>";
        $tableContent .= "<td>" . htmlspecialchars($registro['id']) . "</td>";
        $tableContent .= "<td>" . htmlspecialchars(date('d/m/Y H:i', strtotime($registro['fecha']))) . "</td>";
        $tableContent .= "<td>{$tipo_badge}</td>";
        $tableContent .= "<td>{$cliente_desc}</td>";
        $tableContent .= "<td>{$notas_adicionales}</td>";
        $tableContent .= "<td>{$monto}</td>";
        $tableContent .= "<td>{$usuario_nombre}</td>";
        $tableContent .= "<td>{$acciones}</td>";
        $tableContent .= "</tr>";
    }
}

// Generar paginación
$pagination = '<ul class="pagination justify-content-center">';
if ($pagina_actual > 1) {
    $pagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . ($pagina_actual - 1) . '">Anterior</a></li>';
}
for ($i = 1; $i <= $total_paginas; $i++) {
    $active_class = ($i == $pagina_actual) ? 'active' : '';
    $pagination .= '<li class="page-item ' . $active_class . '"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
}
if ($pagina_actual < $total_paginas) {
    $pagination .= '<li class="page-item"><a class="page-link" href="#" data-page="' . ($pagina_actual + 1) . '">Siguiente</a></li>';
}
$pagination .= '</ul>';

// Devolver como JSON
header('Content-Type: application/json');
echo json_encode(['tableContent' => $tableContent, 'pagination' => $pagination]);

mysqli_close($conexion);
?>