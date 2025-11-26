<?php
require_once 'config/database.php';

// El término de búsqueda se envía por GET desde el script de ventas.php
$search_term = $_GET['searchTerm'] ?? '';

$conn = conectarDB();

// La consulta debe buscar en las columnas relevantes
$sql = "SELECT v.id, v.fecha, v.total, u.nombre AS usuario, v.nombre_cliente, v.detalle_venta 
        FROM ventas v 
        JOIN usuarios u ON v.usuario_id = u.id 
        WHERE v.id LIKE ? OR v.fecha LIKE ? OR v.total LIKE ? OR u.nombre LIKE ? OR v.nombre_cliente LIKE ? OR v.detalle_venta LIKE ?
        ORDER BY v.fecha DESC";

$search_param = "%{$search_term}%";
$stmt = mysqli_prepare($conn, $sql);
// Necesitamos 6 parámetros 's' para los 6 LIKEs
mysqli_stmt_bind_param($stmt, "ssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    while ($venta = mysqli_fetch_assoc($result)) {
        echo "<tr id='venta-" . htmlspecialchars($venta['id']) . "'>";
        echo "<td>" . htmlspecialchars($venta['id']) . "</td>";
        echo "<td>" . htmlspecialchars($venta['fecha']) . "</td>";
        echo "<td>" . htmlspecialchars($venta['nombre_cliente']) . "</td>";
        echo "<td>" . htmlspecialchars($venta['detalle_venta']) . "</td>";
        echo "<td>$" . number_format($venta['total'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($venta['usuario']) . "</td>";
        echo "<td>";
        echo "    <a href='generar_pdf_venta.php?id=" . $venta['id'] . "' class='btn btn-danger btn-sm' target='_blank'>";
        echo "        <i class='fas fa-file-pdf'></i> PDF";
        echo "    </a>";
        echo "    <button class='btn btn-warning btn-sm delete-sale' data-id='" . $venta['id'] . "'>";
        echo "        <i class='fas fa-trash'></i> Borrar";
        echo "    </button>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>No se encontraron ventas que coincidan con la búsqueda.</td></tr>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>