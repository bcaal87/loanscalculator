<?php
require('fpdf186/fpdf.php');

// Función para generar la tabla de pagos y retornar los datos
function generar_tabla_pagos($monto_prestamo, $plazo_meses, $tipo_credito, $tipo_cuota, $interes)
{
    $tabla_pagos = array();

    if ($tipo_cuota == 'saldos') {
        if ($plazo_meses != 0) {
            $tasa_interes_decimal = $interes / 100 / 12;
            $cuota = $monto_prestamo / $plazo_meses;

            for ($i = 1; $i <= $plazo_meses; $i++) {
                $saldo_restante = $monto_prestamo - ($cuota * $i);
                $intereses_cuota_actual = $saldo_restante * $tasa_interes_decimal;
                $cuota_actual = $cuota + $intereses_cuota_actual;

                $tabla_pagos[] = array(
                    'No. Cuota' => $i,
                    'Cuota Capital' => round($cuota, 2),
                    'Interés' => round($intereses_cuota_actual, 2),
                    'Cuota Total' => round($cuota_actual, 2),
                    'Saldo del Préstamo' => round($saldo_restante, 2)
                );
            }
        }
    }

    return $tabla_pagos;
}

// Función para generar el PDF con la tabla de pagos
function generar_pdf($tabla_pagos, $monto_prestamo, $plazo_meses, $tipo_credito, $tipo_cuota, $nombre_cliente, $telefono_cliente, $correo_cliente, $dpi_cliente)
{
    class PDF extends FPDF
    {
        private $monto_prestamo;
        private $plazo_meses;
        private $tipo_credito;
        private $tipo_cuota;
        private $nombre_cliente;
        private $telefono_cliente;
        private $correo_cliente;
        private $dpi_cliente;

        // Constructor
        public function __construct($monto_prestamo, $plazo_meses, $tipo_credito, $tipo_cuota, $nombre_cliente, $telefono_cliente, $correo_cliente, $dpi_cliente)
        {
            parent::__construct();
            $this->monto_prestamo = $monto_prestamo;
            $this->plazo_meses = $plazo_meses;
            $this->tipo_credito = $tipo_credito;
            $this->tipo_cuota = $tipo_cuota;
            $this->nombre_cliente = $nombre_cliente;
            $this->telefono_cliente = $telefono_cliente;
            $this->correo_cliente = $correo_cliente;
            $this->dpi_cliente = $dpi_cliente;
        }

        // Cabecera de página
        function Header()
        {
            // Logo (si tienes uno)

            // Títulos
            $this->SetFont('Arial', 'B', 10); // Fuente en negrita y tamaño 12
            $this->Cell(0, 10, 'Plan de Pagos', 0, 1, 'C'); // Título centrado
            $this->SetFont('Arial', 'B', 10); // Fuente en negrita y tamaño 10
            $this->Cell(0, 10, 'Plan de Pagos Cooperativa', 0, 1, 'C'); // Subtítulo centrado

            // Información de contacto
            $this->SetFont('Arial', 'I', 8); // Fuente en cursiva y tamaño 8
            $this->Cell(0, 5, 'Email: ', 0, 1, 'C'); // Email centrado
            $this->Cell(0, 5, 'Tel: ', 0, 1, 'C'); // Teléfono centrado

            // Información del crédito y cliente
            $this->SetFont('Arial', 'B', 8); // Restaurar fuente normal y tamaño 10
            $this->Cell(0, 5, utf8_decode('Monto del crédito:  Q') . number_format($this->monto_prestamo, 2), 0, 1, 'L'); // Alinear a la izquierda
            $this->Cell(190, 5, utf8_decode('Plazo en meses:  ') . $this->plazo_meses, 0, 1, 'D'); // Alinear a la izquierda
            $this->Cell(190, 5, utf8_decode('Tipo de crédito:  ') . $this->tipo_credito, 0, 1, 'L'); // Alinear a la izquierda
            $this->Cell(190, 5, utf8_decode('Tipo de cuota:  ') . $this->tipo_cuota, 0, 1, 'D'); // Alinear a la izquierda
            $this->Cell(0, 5, utf8_decode('Correo del cliente:  ') . $this->correo_cliente, 0, 1, 'L'); // Alinear a la izquierda
            $this->Line(0, 40, 210, 40);


            $this->Ln(10); // Separación entre la cabecera y el contenido
        }


        // Pie de página
        function Footer()
        {
            // Posición: a 1,5 cm del final
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            // Número de página
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }

    $pdf = new PDF($monto_prestamo, $plazo_meses, $tipo_credito, $tipo_cuota, $nombre_cliente, $telefono_cliente, $correo_cliente, $dpi_cliente);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // Encabezado


    // Cabecera de la tabla
    $pdf->SetFillColor(189, 215, 238); // Color de fondo azul claro
    $pdf->SetTextColor(10, 86, 146); // Color de texto azul
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 10, utf8_decode('No. Cuota'), 0, 0, 'C', true); // Eliminado el borde y centrado
    $pdf->Cell(30, 10, utf8_decode('Cuota Capital'), 0, 0, 'C', true); // Eliminado el borde y centrado
    $pdf->Cell(30, 10, utf8_decode('Interés'), 0, 0, 'C', true); // Eliminado el borde y centrado
    $pdf->Cell(30, 10, utf8_decode('Cuota Total'), 0, 0, 'C', true); // Eliminado el borde y centrado
    $pdf->Cell(40, 10, utf8_decode('Saldo del Préstamo'), 0, 0, 'C', true); // Eliminado el borde y centrado
    $pdf->Ln();

    // Crear la tabla con los datos reales
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(224, 235, 255); // Color de fondo azul suave para filas pares
    $fill = false;
    foreach ($tabla_pagos as $fila) {
        $pdf->SetTextColor(0); // Restaurar color de texto predeterminado
        $pdf->Cell(30, 10, $fila['No. Cuota'], 0, 0, 'C', $fill); // Eliminado el borde y centrado
        $pdf->Cell(30, 10, 'Q' . number_format($fila['Cuota Capital'], 2), 0, 0, 'C', $fill); // Eliminado el borde y centrado
        $pdf->Cell(30, 10, 'Q' . number_format($fila['Interés'], 2), 0, 0, 'C', $fill); // Eliminado el borde y centrado
        $pdf->Cell(30, 10, 'Q' . number_format($fila['Cuota Total'], 2), 0, 0, 'C', $fill); // Eliminado el borde y centrado
        $pdf->Cell(40, 10, 'Q' . number_format($fila['Saldo del Préstamo'], 2), 0, 0, 'C', $fill); // Eliminado el borde y centrado
        $pdf->Ln();
        $fill = !$fill;
    }


    // Pie de página

    // Salida del PDF
    $pdf->Output('TablaPagos.pdf', 'D');
}

// Verificar si se ha enviado el formulario principal
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario principal
    $monto_prestamo = isset($_POST["monto_prestamo"]) ? $_POST["monto_prestamo"] : 0;
    $plazo_meses = isset($_POST["plazo_meses"]) ? $_POST["plazo_meses"] : 0;
    $tipo_credito = isset($_POST["tipo_credito"]) ? $_POST["tipo_credito"] : '';
    $tipo_cuota = isset($_POST["tipo_cuota"]) ? $_POST["tipo_cuota"] : '';
    $nombre_cliente = isset($_POST["nombre"]) ? $_POST["nombre"] : '';
    $telefono_cliente = isset($_POST["telefono"]) ? $_POST["telefono"] : '';
    $correo_cliente = isset($_POST["correo"]) ? $_POST["correo"] : '';
    $dpi_cliente = isset($_POST["dpi"]) ? $_POST["dpi"] : '';

    // Calcular los intereses según el tipo de crédito
    switch ($tipo_credito) {
        case 'vehiculo':
            $interes = 5;
            break;
        case 'agricola':
            $interes = 7;
            break;
        case 'consumo':
            $interes = 10;
            break;
        default:
            $interes = 0;
    }

    // Generar la tabla de pagos
    $tabla_pagos = generar_tabla_pagos($monto_prestamo, $plazo_meses, $tipo_credito, $tipo_cuota, $interes);

    // Generar el PDF con la tabla de pagos
    generar_pdf($tabla_pagos, $monto_prestamo, $plazo_meses, $tipo_credito, $tipo_cuota, $nombre_cliente, $telefono_cliente, $correo_cliente, $dpi_cliente);
} else {
    echo "No se han recibido datos del formulario.";
}
