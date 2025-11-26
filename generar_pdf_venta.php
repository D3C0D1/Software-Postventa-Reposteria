<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'lib/fpdf/fpdf.php';

if (!isset($_GET['id'])) {
    die('ID de venta no proporcionado.');
}

$venta_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$venta_id) {
    die('ID de venta inválido.');
}

$db = conectarDB();

// Obtener datos de la configuración de la empresa
$config_result = mysqli_query($db, "SELECT * FROM configuracion WHERE id = 1");
$config = mysqli_fetch_assoc($config_result) ?: [];

// Obtener datos de la venta
$stmt_venta = mysqli_prepare($db, "SELECT v.id, v.fecha, v.total, v.nombre_cliente, v.detalle_venta, u.nombre as usuario FROM ventas v JOIN usuarios u ON v.usuario_id = u.id WHERE v.id = ?");
mysqli_stmt_bind_param($stmt_venta, 'i', $venta_id);
mysqli_stmt_execute($stmt_venta);
$venta_result = mysqli_stmt_get_result($stmt_venta);
$venta = mysqli_fetch_assoc($venta_result);
mysqli_stmt_close($stmt_venta);

if (!$venta) {
    die('Venta no encontrada.');
}

// Obtener detalles de la venta
$stmt_detalle = mysqli_prepare($db, "SELECT 
    vd.id as detalle_id, 
    vd.cantidad, 
    vd.precio_unitario, 
    (vd.cantidad * vd.precio_unitario) as subtotal, 
    p.nombre as producto_nombre, 
    sp.nombre as subproducto_nombre 
FROM 
    venta_detalles vd 
LEFT JOIN 
    productos p ON vd.producto_id = p.id 
LEFT JOIN 
    subproductos sp ON vd.subproducto_id = sp.id 
WHERE 
    vd.venta_id = ?");

mysqli_stmt_bind_param($stmt_detalle, 'i', $venta_id);
mysqli_stmt_execute($stmt_detalle);
$detalles_result = mysqli_stmt_get_result($stmt_detalle);

// --- Inicio de la generación del PDF para Ticket ---

// Ancho del ticket en mm (80mm es un estándar para impresoras POS)
$ticket_width = 80;

// Crear una clase PDF personalizada para el ticket
class TicketPDF extends FPDF
{
    private $config;

    function __construct($config = []) {
        // El alto de la página se puede ajustar dinámicamente, empezamos con un valor alto
        parent::__construct('P', 'mm', [$GLOBALS['ticket_width'], 200]);
        $this->config = $config;
        $this->SetMargins(5, 5, 5);
        $this->SetAutoPageBreak(true, 5);
    }

    function Header() {
        // Logo
        $logo_path = 'public/uploads/logo.png'; // Asegúrate que esta ruta sea correcta
        if (!empty($logo_path) && file_exists($logo_path)) {
            $this->Image($logo_path, ($this->GetPageWidth() - 20) / 2, 5, 20);
            $this->Ln(22);
        }

        // Nombre de la empresa y datos
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, utf8_decode($this->config['nombre_empresa'] ?? 'Nombre de Empresa'), 0, 1, 'C');
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 4, 'NIT: ' . ($this->config['nit'] ?? 'N/A'), 0, 1, 'C');
        $this->MultiCell(0, 4, utf8_decode($this->config['direccion'] ?? 'Direccion no disponible'), 0, 'C');
        $this->Ln(3);
        $this->Line($this->GetX(), $this->GetY(), $this->GetX() + $this->GetPageWidth() - 10, $this->GetY());
        $this->Ln(3);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, utf8_decode('¡Gracias por su compra!'), 0, 1, 'C');
    }
}

$pdf = new TicketPDF($config);
$pdf->AddPage();

// Información de la venta
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 4, 'Ticket Nro: ' . $venta['id'], 0, 1);
$pdf->Cell(0, 4, 'Fecha: ' . date('d/m/Y H:i:s', strtotime($venta['fecha'])), 0, 1);
$pdf->Cell(0, 4, 'Cliente: ' . utf8_decode($venta['nombre_cliente']), 0, 1);
$pdf->Cell(0, 4, 'Atendido por: ' . utf8_decode($venta['usuario']), 0, 1);
$pdf->Ln(3);

// Línea divisoria
$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + $pdf->GetPageWidth() - 10, $pdf->GetY());
$pdf->Ln(2);

// Cabecera de productos
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(35, 5, 'Producto', 0, 0);
$pdf->Cell(10, 5, 'Cant', 0, 0, 'C');
$pdf->Cell(15, 5, 'Precio', 0, 0, 'R');
$pdf->Cell(10, 5, 'Total', 0, 1, 'R');
$pdf->Ln(1);

// Almacenar detalles para procesarlos
$detalles = [];
while ($detalle = mysqli_fetch_assoc($detalles_result)) {
    $detalles[] = $detalle;
}

// Productos
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(0, 5, 'Productos', 0, 1);
$pdf->SetFont('Arial', '', 8);
foreach ($detalles as $detalle) {
    if ($detalle['producto_nombre']) {
        $nombre_producto = $detalle['producto_nombre'];
        $lineHeight = 4;
        $y0 = $pdf->GetY();
        $x0 = $pdf->GetX();
        $pdf->MultiCell(35, $lineHeight, utf8_decode($nombre_producto), 0, 'L');
        $rowHeight = $pdf->GetY() - $y0;
        $pdf->SetXY($x0 + 35, $y0);
        $pdf->Cell(10, $rowHeight, $detalle['cantidad'], 0, 0, 'C');
        $pdf->Cell(15, $rowHeight, '$' . number_format($detalle['precio_unitario'], 0), 0, 0, 'R');
        $pdf->Cell(10, $rowHeight, '$' . number_format($detalle['subtotal'], 0), 0, 1, 'R');
    }
}

// Adiciones
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(0, 5, 'Adiciones', 0, 1);
$pdf->SetFont('Arial', '', 8);
foreach ($detalles as $detalle) {
    if ($detalle['subproducto_nombre']) {
        $nombre_producto = $detalle['subproducto_nombre'];
        $lineHeight = 4;
        $y0 = $pdf->GetY();
        $x0 = $pdf->GetX();
        $pdf->MultiCell(35, $lineHeight, utf8_decode($nombre_producto), 0, 'L');
        $rowHeight = $pdf->GetY() - $y0;
        $pdf->SetXY($x0 + 35, $y0);
        $pdf->Cell(10, $rowHeight, $detalle['cantidad'], 0, 0, 'C');
        $pdf->Cell(15, $rowHeight, '$' . number_format($detalle['precio_unitario'], 0), 0, 0, 'R');
        $pdf->Cell(10, $rowHeight, '$' . number_format($detalle['subtotal'], 0), 0, 1, 'R');
    }
}

// Línea divisoria
$pdf->Ln(2);
$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + $pdf->GetPageWidth() - 10, $pdf->GetY());
$pdf->Ln(2);

// Total
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(45, 6, 'TOTAL:', 0, 0, 'R');
$pdf->Cell(25, 6, '$' . number_format($venta['total'], 2), 0, 1, 'R');

// Detalles adicionales
if (!empty($venta['detalle_venta'])) {
    $pdf->Ln(3);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 4, 'Notas:', 0, 1);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(0, 4, utf8_decode($venta['detalle_venta']), 0, 'L');
}

mysqli_close($db);
$pdf->Output('I', 'ticket_venta_' . $venta_id . '.pdf');
?>