<?php
// generar_requisicion.php

// 1. Incluir la librería FPDF
require('fpdf.php'); // Asegúrate de que esta ruta sea correcta

// 2. Clase personalizada para el formato (maneja cabecera y pie de página)
class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // CONFECCIONES TELY, S.A.
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'CONFECCIONES TELY, S.A.', 0, 1, 'C');
        
        // Ruc. 129391-9283
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 4, 'Ruc. 129391-9283', 0, 1, 'C');
        
        // REQUISICIÓN DE COMPRA y No: 0001
        $this->SetY(25);
        $this->SetX(40);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(120, 7, 'REQUISICIÓN DE COMPRA', 1, 0, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(20, 7, 'No: 0001', 'LBR', 1, 'C');
        $this->Ln(3);
    }

    // Pie de página (simulación de datos de Nicaragua)
    function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('Arial', 'B', 8);
        
        // Celdas para Firmas (con saltos de línea para simular el espacio)
        $this->Cell(65, 5, 'Elaborado Por: _________________________', 0, 0, 'L');
        $this->Cell(65, 5, 'Autorizado Por: _________________________', 0, 0, 'L');
        $this->Cell(60, 5, 'Recibido Por: _________________________', 0, 1, 'L');

        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 3, 'Imprenta San Sebastian, Managua - Nicaragua Tel: 289 3643 Fax: 289 7364', 0, 1, 'C');
        $this->Cell(0, 4, 'Figura 1: Formato de requisición de compra', 0, 1, 'C');
    }
}

// 3. Inicialización del documento
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 30); // Ajusta el salto de página

// --- SIMULACIÓN DE DATOS (En tu caso, serían los datos de la orden obtenida en obtener_detalle.php) ---
// La requisición de compra se basa en el total de una orden
$fecha_pedido = '2025-11-30'; // Usar la fecha de la orden
$fecha_entrega = '2025-12-05'; 
$departamento = 'PRODUCCIÓN'; // Asumiendo que el pedido es para producción
$articulos_orden = [
    // Estos datos simulan los de la imagen
    ['cantidad' => 1500, 'unidad' => 'yardas', 'articulo' => 'Tela blanca 60% algodón y 40% poliester'],
    ['cantidad' => 300, 'unidad' => 'yardas', 'articulo' => 'Tela amarilla 60% algodón y 40% poliester'],
    ['cantidad' => 10, 'unidad' => 'rollos', 'articulo' => 'Hilo blanco core 40 kobav 40'],
    ['cantidad' => 2, 'unidad' => 'rollos', 'articulo' => 'Hilo amarillo core 40 kobav 40'],
    ['cantidad' => 7000, 'unidad' => 'unidad', 'articulo' => 'Botones blancos N° 18'],
    ['cantidad' => 1400, 'unidad' => 'unidad', 'articulo' => 'Botones amarillos N° 18'],
    ['cantidad' => 1200, 'unidad' => 'unidad', 'articulo' => 'Etiquetas'],
];
// -------------------------------------------------------------------------------------------------

// 4. Detalle de Requisición
$pdf->SetFont('Arial', '', 9);

// Línea 1: DEPARTAMENTO, FECHA DE PEDIDO, FECHA DE ENTREGA
$pdf->Cell(45, 6, 'DEPTO QUE SOLICITA:', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(55, 6, $departamento, 'B', 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(40, 6, 'FECHA DE ENTREGA:', 0, 0, 'R');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 6, date('d/m/Y', strtotime($fecha_entrega)), 'B', 1, 'L');

// Línea 2: FECHA DEL PEDIDO
$pdf->Cell(45, 6, 'FECHA DEL PEDIDO:', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(55, 6, date('d/m/Y', strtotime($fecha_pedido)), 'B', 1, 'L');


// 5. Encabezado de la Tabla de Artículos
$pdf->Ln(4);
$pdf->SetFillColor(200, 200, 200); // Color de fondo para encabezados
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 7, 'CANTIDAD', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'UNIDAD', 1, 0, 'C', true);
$pdf->Cell(130, 7, 'ARTÍCULOS', 1, 1, 'C', true);

// 6. Cuerpo de la Tabla de Artículos
$pdf->SetFont('Arial', '', 9);
$fill = false;
foreach ($articulos_orden as $item) {
    $pdf->Cell(30, 6, number_format($item['cantidad'], 0, ',', '.'), 'LR', 0, 'R', $fill);
    $pdf->Cell(30, 6, $item['unidad'], 'R', 0, 'L', $fill);
    $pdf->Cell(130, 6, $item['articulo'], 'R', 1, 'L', $fill);
    $fill = !$fill; // Para alternar colores de fila si se desea
}

// Rellenar con filas vacías hasta el final de la tabla (solo borde)
// Esto es para simular el espacio vacío en el formato de la imagen
$num_filas = count($articulos_orden);
$max_filas = 10; // Número deseado de filas en la tabla
for ($i = $num_filas; $i < $max_filas; $i++) {
    $pdf->Cell(30, 6, '', 'LR', 0, 'C');
    $pdf->Cell(30, 6, '', 'R', 0, 'C');
    $pdf->Cell(130, 6, '', 'R', 1, 'C');
}

// Borde inferior de la tabla
$pdf->Cell(190, 0, '', 'T', 1, 'C');


// 7. Salida del PDF
$pdf->Output('I', 'Requisicion_Compra_' . $fecha_pedido . '.pdf');
?>