<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'lib/fpdf/fpdf.php';

if (!isset($_GET['id'])) {
    die('Venta no especificada.');
}

$venta_id = intval($_GET['id']);
$db = conectarDB();

// Obtener configuración de la empresa
$config_result = $db->query("SELECT * FROM configuracion WHERE id = 1");
$config = $config_result->fetch_assoc();

// Obtener datos de la venta, incluyendo las notas
$stmt = $db->prepare("SELECT v.id, v.fecha, v.total, v.nombre_cliente, v.detalle_venta, u.nombre as nombre_vendedor FROM ventas v JOIN usuarios u ON v.usuario_id = u.id WHERE v.id = ?");
$stmt->bind_param('i', $venta_id);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$venta) {
    die('Venta no encontrada.');
}

// Obtener detalles de la venta
$stmt = $db->prepare("SELECT vd.cantidad, vd.precio_unitario, p.nombre as producto_nombre, sp.nombre as subproducto_nombre FROM venta_detalles vd LEFT JOIN productos p ON vd.producto_id = p.id LEFT JOIN subproductos sp ON vd.subproducto_id = sp.id WHERE vd.venta_id = ?");
$stmt->bind_param('i', $venta_id);
$stmt->execute();
$detalles = $stmt->get_result();

class PDF extends FPDF
{
    private $config;

    function __construct($orientation='P', $unit='mm', $size='A4', $config_data = []) {
        parent::__construct($orientation, $unit, $size);
        $this->config = $config_data;
        $this->SetAutoPageBreak(true, 15);
    }

    function Header()
    {
        if (!empty($this->config['logo_url']) && file_exists($this->config['logo_url'])) {
            $this->Image($this->config['logo_url'], ($this->GetPageWidth() - 25) / 2, 8, 25);
            $this->Ln(28);
        }
        $this->SetFont('Arial','B',10);
        $this->Cell(0,5,utf8_decode($this->config['nombre_empresa']),0,1,'C');
        $this->SetFont('Arial','',8);
        $this->Cell(0,4,'NIT: ' . $this->config['nit'],0,1,'C');
        $this->Cell(0,4,utf8_decode($this->config['direccion']),0,1,'C');
        $this->Ln(3);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','B',8);
        $this->Cell(0,10,utf8_decode('¡Gracias por su compra!'),0,0,'C');
    }
}

// Ancho del ticket: 80mm. La altura es dinámica.
$pdf = new PDF('P', 'mm', [80, 250], $config);
$pdf->AddPage();
$pdf->SetFont('Arial','',8);
$pdf->SetMargins(5, 5, 5);

// Info de la factura
$pdf->Cell(0,4,'Factura Nro: ' . $venta['id'],0,1);
$pdf->Cell(0,4,'Fecha: ' . date('d/m/Y H:i:s', strtotime($venta['fecha'])),0,1);
$pdf->Cell(0,4,'Cliente: ' . utf8_decode($venta['nombre_cliente']),0,1);
$pdf->Cell(0,4,'Vendedor: ' . utf8_decode($venta['nombre_vendedor']),0,1);
$pdf->Ln(3);

// Línea divisoria
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
$pdf->Ln(1);

// Encabezados de la tabla de productos
$pdf->SetFont('Arial','B',8);
$pdf->Cell(40,6,'Producto',0,0,'L');
$pdf->Cell(10,6,'Cant',0,0,'C');
$pdf->Cell(20,6,'Precio',0,1,'R');

// Línea divisoria
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
$pdf->Ln(1);

$pdf->SetFont('Arial','',8);
while($detalle = $detalles->fetch_assoc()){
    $nombre_item = $detalle['producto_nombre'] ? $detalle['producto_nombre'] : $detalle['subproducto_nombre'];
    $y_before = $pdf->GetY();
    $pdf->MultiCell(40, 4, utf8_decode($nombre_item), 0, 'L');
    $y_after = $pdf->GetY();
    $pdf->SetXY($pdf->GetX() + 40, $y_before);
    $line_height = $y_after - $y_before;
    $pdf->Cell(10, $line_height, $detalle['cantidad'],0,0,'C');
    $pdf->Cell(20, $line_height, '$' . number_format($detalle['precio_unitario'], 0, ',', '.'),0,1,'R');
}

// Línea divisoria
$pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
$pdf->Ln(1);

// Total
$pdf->SetFont('Arial','B',9);
$pdf->Cell(50,7,'Total',0,0,'R');
$pdf->Cell(20,7,'$' . number_format($venta['total'], 0, ',', '.'),0,1,'R');
$pdf->Ln(5);

// Notas de la venta
if (!empty($venta['detalle_venta'])) {
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(0,5,'Notas:',0,1);
    $pdf->SetFont('Arial','',8);
    $pdf->MultiCell(0,4,utf8_decode($venta['detalle_venta']),0,'L');
    $pdf->Ln(3);
}

$stmt->close();
$db->close();

$pdf->Output('I', 'factura_venta_' . $venta_id . '.pdf');

?>