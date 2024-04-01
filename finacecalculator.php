<?php
/*
Plugin Name: Calculadora Financiera
Description: Calculadora para generar tablas de amortización Cuota Nivelada y Cuota Sobre Saldos.
Version: 1.0
Author: Prolink GT
License: GPL v2 or later
*/

// Función para mostrar el formulario de la calculadora financiera
function mostrar_calculadora_financiera()
{
    // Definir las variables iniciales
    $ultima_cuota = 0; // Inicializamos $ultima_cuota con un valor predeterminado
    $monto_prestamo = 0;
    $plazo_meses = 0;
    $tipo_credito = '';
    $tipo_cuota = '';
    $interes = 0;
    $cuota = 0;
    $error_message = '';


    // Verificar si se ha enviado el formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtener los datos del formulario y validarlos
        $monto_prestamo = isset($_POST["monto_prestamo"]) ? filter_var($_POST["monto_prestamo"], FILTER_SANITIZE_NUMBER_FLOAT) : 0;
        $plazo_meses = isset($_POST["plazo_meses"]) ? filter_var($_POST["plazo_meses"], FILTER_SANITIZE_NUMBER_INT) : 0;
        $tipo_credito = isset($_POST["tipo_credito"]) ? $_POST["tipo_credito"] : '';
        $tipo_cuota = isset($_POST["tipo_cuota"]) ? $_POST["tipo_cuota"] : '';

        // Validar los datos del formulario
        if ($monto_prestamo <= 0 || $plazo_meses <= 0 || empty($tipo_credito) || empty($tipo_cuota)) {
            $error_message = "Por favor, ingresa todos los datos correctamente.";
        } else {
            // Calcular los intereses según el tipo de crédito
            switch ($tipo_credito) {
                case 'vehiculo':
                    $interes = 18; // Tasa de interés para crédito vehicular ( Tasa de ejemplo)
                    break;
                case 'agricola':
                    $interes = 15; // Tasa de interés para crédito agrícola (Tasas de ejemplo)
                    break;
                case 'consumo':
                    $interes = 18; // Tasa de interés para crédito de consumo (Tasas ejemplo)
                    break;
                default:
                    $interes = 0;
            }

            // Calcular la cuota según el tipo de cuota
            if ($tipo_cuota == 'nivelada') {
                $tasa_interes_decimal = $interes / 100 / 12;
                $num_cuotas = $plazo_meses;
                $cuota = ($tasa_interes_decimal != 0) ? ($monto_prestamo * $tasa_interes_decimal) / (1 - pow(1 + $tasa_interes_decimal, -$num_cuotas)) : $monto_prestamo / $plazo_meses;
            } elseif ($tipo_cuota == 'saldos') {
                if ($plazo_meses != 0) {
                    $tasa_interes_decimal = $interes / 100 / 12;
                    $cuota = $monto_prestamo / $plazo_meses;
                    $intereses_primera_cuota = $monto_prestamo * $tasa_interes_decimal;
                    $primera_cuota = $cuota + $intereses_primera_cuota;

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
                        $ultima_cuota = $cuota_actual;
                    }
                } else {
                    $error_message = "El plazo no puede ser cero.";
                }
            }

            // Validar que la cuota esté calculada correctamente
            if ($cuota <= 0) {
                $error_message = "Error al calcular la cuota. Por favor, verifica los datos ingresados.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotiza tu crédito con nosotros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--Estilos-->
    <style>
        body {
            background-color: #ffffff; /* Cambia el color de fondo del cuerpo a blanco */
        }

        .container {
            background-color: #ffffff; /* Cambia el color de fondo del contenedor a blanco */
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            padding: 20px; /* Ajusta el padding del contenedor */
            margin-top: 20px;
        }

        .resumen-pagos {
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff; /* Cambia el color de fondo del resumen de pagos a blanco */
        }

        .resumen-pagos h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #003f86; /* Cambia el color del título del resumen de pagos a azul */
        }

        .resumen-pagos p {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .resumen-pagos-container {
            max-width: 100%;
            margin: 0 auto; /* Centra horizontalmente */
            overflow-x: auto; /* Agrega barra de desplazamiento horizontal si es necesario */
        }

        table {
            font-size: 14px; /* Reducido el tamaño de la fuente de la tabla */
        }

        th,
        td {
            padding: 5px; /* Reducido el padding para ajustar el tamaño de las celdas */
        }

        .btn {
            padding: 5px 10px; /* Reducido el padding de los botones para hacerlos más pequeños */
            font-size: 14px; /* Reducido el tamaño de la fuente de los botones */
        }

        .custom-table {
            color: #003f86; /* Texto azul */
        }

        .custom-table th {
            background-color: #003f86; /* Cabecera azul */
            color: #ffffff; /* Texto blanco */
        }

        .table-header-strong-blue th {
            background-color: #003366; /* Cabecera azul fuerte */
            color: #ffffff; /* Texto blanco */
        }

        .input-group-text {
            background-color: #00b01a; /* Cambia el color de fondo del texto del input group a verde */
            color: #ffffff; /* Cambia el color del texto del input group a blanco */
            border-color: #00b01a; /* Cambia el color del borde del input group a verde */
        }
        </style>

</head>

<body>
    <div class="container container-sm" style="padding: 20px;">
        <div class="row">
            <div class="col-lg-6">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>">
                    <div class="mb-2">
                        <label for="monto_prestamo" class="form-label">Monto del crédito (Q):</label>
                        <div class="input-group">
                            <span class="input-group-text">Q</span>
                            <input type="number" name="monto_prestamo" class="form-control form-control-sm" value="<?php echo $monto_prestamo; ?>" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="plazo_meses" class="form-label">Plazo en meses:</label>
                        <input type="number" name="plazo_meses" class="form-control form-control-sm" value="<?php echo $plazo_meses; ?>" required>
                    </div>
                    <div class="mb-2">
                        <label for="tipo_credito" class="form-label">Tipo de crédito:</label>
                        <select name="tipo_credito" class="form-select form-select-sm" required>
                            <option value="vehiculo" <?php if ($tipo_credito == 'vehiculo') echo 'selected'; ?>>Vehículo</option>
                            <option value="agricola" <?php if ($tipo_credito == 'agricola') echo 'selected'; ?>>Agrícola</option>
                            <option value="consumo" <?php if ($tipo_credito == 'consumo') echo 'selected'; ?>>Consumo</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="tipo_cuota" class="form-label">Tipo de cuota:</label>
                        <select name="tipo_cuota" class="form-select form-select-sm" required>
                            <option value="nivelada" <?php if ($tipo_cuota == 'nivelada') echo 'selected'; ?>>Nivelada</option>
                            <option value="saldos" <?php if ($tipo_cuota == 'saldos') echo 'selected'; ?>>Sobre Saldos</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Calcular</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="mostrarTablaPagos()" <?php if ($tipo_cuota == 'nivelada') echo 'disabled'; ?>>Ver tabla de pagos</button>
                </form>
            </div>

            <div class="col-lg-6 d-flex justify-content-center align-items-center">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && $error_message == '') : ?>
                    <div class="resumen-pagos">
                        <!-- Contenedor adicional -->
                        <div class="resumen-pagos-container">
                            <h2>Resumen de Pagos:</h2>
                            <?php if ($tipo_cuota == 'nivelada') : ?>
                                <p><b>Cuota Nivelada:</b></p>
                                <p>a) Cuota a Pagar: <b>Q. <?php echo number_format($cuota, 2); ?></b></p>
                                <p>b) Intereses Totales: <b>Q. <?php echo number_format(($cuota * $plazo_meses) - $monto_prestamo, 2); ?></b></p>
                                <p>c) Monto Total del Crédito: Q. <?php echo number_format($monto_prestamo, 2); ?></p>
                                <p>d) Tasa del Crédito: <?php echo $interes; ?>%</p>
                            <?php elseif ($tipo_cuota == 'saldos') : ?>
                                <p><b>Cuota Sobre Saldos:</b></p>
                                <?php if ($plazo_meses != 0) : ?>
                                    <p>a) Primera Cuota (Más Intereses): <b>Q. <?php echo number_format($primera_cuota, 2); ?></b></p>
                                    <p>b) Última Cuota (Más Intereses): <b>Q. <?php echo number_format($ultima_cuota, 2); ?></b></p>
                                <?php endif; ?>
                                <p>c) Intereses Totales: <b>Q. <?php echo number_format(($interes / 100 / 12) * $plazo_meses * $monto_prestamo, 2); ?></b></p>
                                <p>d) Tasa del Crédito: <?php echo $interes; ?>%</p>
                            <?php endif; ?>
                            <!-- Cláusula General -->
                            <small>*Estimado asociado, el cálculo de las cuotas es solamente una proyección. Contáctenos para mayor información.</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<!-- Div para mostrar la tabla de pagos -->
<div class="row">
    <div class="col-lg-12">
        <div id="tabla-pagos" style="display: none;">
            <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && $error_message == '' && isset($tabla_pagos) && !empty($tabla_pagos)) : ?>
                <h2 class="mt-5">Tabla de Pagos:</h2>
                <table class="table table-bordered custom-table">
                    <tr class="table-header-strong-blue">
                        <th>No. Cuota</th>
                        <th>Cuota Capital</th>
                        <th>Interés</th>
                        <th>Cuota Total</th>
                        <th>Saldo del Préstamo</th>
                    </tr>
                    <?php foreach ($tabla_pagos as $cuota) : ?>
                        <tr>
                            <?php foreach ($cuota as $clave => $valor) : ?>
                                <?php if ($clave === 'Cuota Capital' || $clave === 'Interés' || $clave === 'Cuota Total' || $clave === 'Saldo del Préstamo') : ?>
                                    <td>Q. <?php echo $valor; ?></td>
                                <?php else : ?>
                                    <td><?php echo $valor; ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>


    <script>
        function mostrarTablaPagos() {
            var tablaPagos = document.getElementById('tabla-pagos');
            if (tablaPagos.style.display === 'none') {
                tablaPagos.style.display = 'block';
            } else {
                tablaPagos.style.display = 'none';
            }
        }
    </script>
    <!-- Enlace al archivo JavaScript de Bootstrap (opcional, solo si necesitas funcionalidades JS de Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
}
//mostrar_calculadora_financiera();
// Agregar la función como shortcode para que se pueda usar en las páginas
add_shortcode('calculadora_financiera', 'mostrar_calculadora_financiera'); 
?>

