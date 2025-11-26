<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$db = conectarDB();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$producto_id = $input['producto_id'] ?? 0;
$salsas_obligatorias = $input['salsas_obligatorias'] ?? 0;
$adiciones_obligatorias = $input['adiciones_obligatorias'] ?? 0;

$response = ['success' => true, 'salsas' => [], 'adiciones' => []];

// Obtener salsas disponibles
if ($salsas_obligatorias > 0) {
    $query_salsas = "SELECT id, nombre FROM subproductos WHERE categoria = 'salsa' AND stock > 0 ORDER BY nombre ASC";
    $result_salsas = $db->query($query_salsas);
    if ($result_salsas) {
        while ($salsa = $result_salsas->fetch_assoc()) {
            $response['salsas'][] = $salsa;
        }
    }
}

// Obtener adiciones disponibles (ingredientes)
if ($adiciones_obligatorias > 0) {
    $query_adiciones = "SELECT id, nombre FROM subproductos WHERE categoria = 'ingrediente' AND stock > 0 ORDER BY nombre ASC";
    $result_adiciones = $db->query($query_adiciones);
    if ($result_adiciones) {
        while ($adicion = $result_adiciones->fetch_assoc()) {
            $response['adiciones'][] = $adicion;
        }
    }
}

$db->close();
echo json_encode($response);
?>