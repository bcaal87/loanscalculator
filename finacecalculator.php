<?php
/*
Plugin Name: Calculadora Financiera
Description: Calculadora para generar tablas de amortización Cuota Nivelada y Cuota Sobre Saldos.
Version: 1.0
Author: Prolink GT
License: GPL v2 or later
*/

// Función para mostrar el formulario de la calculadora financiera
function mostrar_calculadora_financiera() {
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
    // Obtener los datos del formulario
    $monto_prestamo = $_POST["monto_prestamo"];
    $plazo_meses = $_POST["plazo_meses"];
    $tipo_credito = $_POST["tipo_credito"];
    $tipo_cuota = $_POST["tipo_cuota"];

    // Calcular los intereses según el tipo de crédito (Lo ideal es que se pueda parametrizar)
    switch ($tipo_credito) {
        case 'vehiculo':
            $interes = 5; // Tasa de interés para crédito vehicular ( Tasa de ejemplo)
            break;
        case 'agricola':
            $interes = 7; // Tasa de interés para crédito agrícola (Tasas de ejemplo)
            break;
        case 'consumo':
            $interes = 10; // Tasa de interés para crédito de consumo (Tasas ejemplo)
            break;
        default:
            $interes = 0;
    }

    // Calcular la cuota según el tipo de cuota
    if ($tipo_cuota == 'nivelada') {
        $tasa_interes_decimal = $interes / 100 / 12;
        $num_cuotas = $plazo_meses;
        if ($tasa_interes_decimal != 0) {
            $cuota = ($monto_prestamo * $tasa_interes_decimal) / (1 - pow(1 + $tasa_interes_decimal, -$num_cuotas));
        } else {
            $cuota = $monto_prestamo / $plazo_meses;
        }
        } elseif ($tipo_cuota == 'saldos') {
            if ($plazo_meses != 0) {
                // Calcular la tasa de interés mensual
                $tasa_interes_decimal = $interes / 100 / 12;

                // Calcular el monto de cada cuota (capital + intereses)
                $cuota = $monto_prestamo / $plazo_meses;

                // Calcular los intereses de la primera cuota
                $intereses_primera_cuota = $monto_prestamo * $tasa_interes_decimal;

                // Calcular el monto de la primera cuota (capital + intereses)
                $primera_cuota = $cuota + $intereses_primera_cuota;

                // Calcular las cuotas y los intereses restantes
                for ($i = 1; $i <= $plazo_meses; $i++) {
                    // Calcular el saldo restante después de pagar la cuota
                    $saldo_restante = $monto_prestamo - ($cuota * $i);

                    // Calcular los intereses de la cuota actual utilizando el saldo restante
                    $intereses_cuota_actual = $saldo_restante * $tasa_interes_decimal;

                    // Calcular el monto de la cuota actual (capital + intereses)
                    $cuota_actual = $cuota + $intereses_cuota_actual;

                    // Almacenar los datos de la cuota actual en la tabla de pagos
                    $tabla_pagos[] = array(
                        'No. Cuota' => $i,
                        'Cuota Capital' => round($cuota, 2),
                        'Interés' => round($intereses_cuota_actual, 2),
                        'Cuota Total' => round($cuota_actual, 2),
                        'Saldo del Préstamo' => round($saldo_restante, 2)
                    );
                    // La última cuota es igual a la cuota calculada en el último ciclo
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
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Calculadora Financiera</title>
    </head>
    <body>
        <h1>Calculadora Financiera</h1>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>">
            <label for="monto_prestamo">Monto del crédito:</label>
            <input type="number" name="monto_prestamo" value="<?php echo $monto_prestamo; ?>" required><br><br>
            
            <label for="plazo_meses">Plazo en meses:</label>
            <input type="number" name="plazo_meses" value="<?php echo $plazo_meses; ?>" required><br><br>
            
            <label for="tipo_credito">Tipo de crédito:</label>
            <select name="tipo_credito" required>
                <option value="vehiculo" <?php if ($tipo_credito == 'vehiculo') echo 'selected'; ?>>Vehículo</option>
                <option value="agricola" <?php if ($tipo_credito == 'agricola') echo 'selected'; ?>>Agrícola</option>
                <option value="consumo" <?php if ($tipo_credito == 'consumo') echo 'selected'; ?>>Consumo</option>
            </select><br><br>

            <label for="tipo_cuota">Tipo de cuota:</label>
            <select name="tipo_cuota" required>
                <option value="nivelada" <?php if ($tipo_cuota == 'nivelada') echo 'selected'; ?>>Nivelada</option>
                <option value="saldos" <?php if ($tipo_cuota == 'saldos') echo 'selected'; ?>>Sobre Saldos</option>
            </select><br><br>

            <button type="submit">Calcular</button>
            <button type="button" onclick="mostrarTablaPagos()">Ver tabla de pagos</button>
        </form>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && $error_message == ''): ?>
            <h2>Resumen de Pagos:</h2>
            <?php if ($tipo_cuota == 'nivelada'): ?>
                <p>Cuota Nivelada:</p>
                <p>a) Cuota a Pagar: Q<?php echo round($cuota, 2); ?></p>
                <p>b) Intereses Totales: Q<?php echo round(($cuota * $plazo_meses) - $monto_prestamo, 2); ?></p>
                <p>c) Monto Total del Crédito: Q<?php echo round($monto_prestamo, 2); ?></p>
                <p>d) Tasa del Crédito: <?php echo $interes; ?>%</p>
            <?php elseif ($tipo_cuota == 'saldos'): ?>
                <p>Cuota Sobre Saldos:</p>
                <?php if ($plazo_meses != 0): ?>
                    <p>a) Primera Cuota (Más Intereses): Q<?php echo round($primera_cuota, 2); ?></p>
                    <p>b) Última Cuota (Más Intereses): Q<?php echo round($ultima_cuota, 2); ?></p>
                <?php endif; ?>
                <p>c) Intereses Totales: Q<?php echo round(($interes / 100 / 12) * $plazo_meses * $monto_prestamo, 2); ?></p>
                <p>d) Tasa del Crédito: <?php echo $interes; ?>%</p>
            <?php endif; ?>
        <?php endif; ?>

        <div id="tabla-pagos" style="display: none;">
            <?php
            // Mostrar la tabla de pagos si está disponible
            if (isset($tabla_pagos) && !empty($tabla_pagos)) {
                echo '<h2>Tabla de Pagos:</h2>';
                echo '<table border="1">';
                echo '<tr><th>No. Cuota</th><th>Cuota Capital</th><th>Interés</th><th>Cuota Total</th><th>Saldo del Préstamo</th></tr>';
                foreach ($tabla_pagos as $cuota) {
                    echo '<tr>';
                    foreach ($cuota as $valor) {
                        echo '<td>' . $valor . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
            }
            ?>
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
    </body>
    </html>
    <?php
}
mostrar_calculadora_financiera();
// Agregar la función como shortcode para que se pueda usar en las páginas
//add_shortcode('calculadora_financiera', 'mostrar_calculadora_financiera'); Codigo para embeberlo en Wordpress.
?>

