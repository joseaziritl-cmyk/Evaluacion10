<?php
// Inicializar variables
$datos_max = 0;
$nombre_tabla = ''; // Nueva variable para el nombre de la tabla
$media = null;
$error = '';
$numeros_ingresados = [];

// === PROCESAMIENTO DE ESTADOS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Si se envía el número de datos y el nombre (Formulario #1)
    if (isset($_POST['num_datos'])) {
        $num = (int)$_POST['num_datos'];
        $nombre_tabla_temp = trim($_POST['nombre_tabla'] ?? ''); // Obtener el nombre
        
        if ($num > 0 && $num <= 50) { // Límite para la demostración
            $datos_max = $num;
            // Asignar el nombre, si no hay uno, usar un valor por defecto
            $nombre_tabla = !empty($nombre_tabla_temp) ? htmlspecialchars($nombre_tabla_temp) : 'Datos de Muestra'; 
        } else {
            $error = "Por favor, introduce un número entre 1 y 50.";
        }
        
    } 
    
    // Si se envían los datos para calcular (Formulario #2)
    elseif (isset($_POST['calcular_media']) && isset($_POST['datos_max_hidden'])) {
        $datos_max = (int)$_POST['datos_max_hidden'];
        $nombre_tabla = htmlspecialchars($_POST['nombre_tabla_hidden'] ?? 'Datos de Muestra'); // Recuperar el nombre
        
        for ($i = 1; $i <= $datos_max; $i++) {
            $key = 'dato_' . $i;
            $valor = $_POST[$key] ?? '';
            
            // La validación is_numeric() sigue siendo esencial
            if (is_numeric($valor) && trim($valor) !== '') {
                $numeros_ingresados[] = (float)$valor;
            }
        }

        // Realizar el cálculo de la Estimación Puntual (Media)
        if (count($numeros_ingresados) > 0) {
            $suma = array_sum($numeros_ingresados);
            $conteo = count($numeros_ingresados);
            $media = $suma / $conteo;
        } else {
            $error = "No se introdujeron datos válidos. Inténtalo de nuevo.";
            $datos_max = 0; // Volver al estado inicial
            $nombre_tabla = ''; // Restablecer el nombre
        }
    }
}
?>

<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Hoja de Cálculo</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 650px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #107c10; text-align: center; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        
        /* Estilos de la tabla de entrada (Estilo Excel) */
        table.excel-grid { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        .excel-grid th, .excel-grid td { border: 1px solid #c8c8c8; padding: 0; height: 25px; }
        
        /* Encabezados de Columna (Estilo A, B, C...) */
        .excel-grid thead th { background-color: #e6e6e6; color: #333; font-weight: bold; border-color: #999; text-align: center; }
        
        /* Encabezados de Fila (Números 1, 2, 3...) */
        .excel-grid tbody th { width: 40px; background-color: #e6e6e6; color: #333; font-weight: bold; text-align: center; border-color: #999; }
        
        /* Celdas de Datos */
        .excel-grid td { text-align: right; background-color: #ffffff; }
        .excel-grid input[type="text"],
        .excel-grid input[type="number"] { 
            width: 100%; height: 25px; padding: 0 5px; 
            border: none; text-align: right; box-sizing: border-box; 
            font-family: inherit; font-size: 14px;
        }
        .excel-grid input[type="text"]:focus,
        .excel-grid input[type="number"]:focus {
            outline: 2px solid #0078d4; /* Resaltar celda activa */
            box-shadow: 0 0 5px rgba(0, 120, 212, 0.5);
        }

        /* Estilos de la tabla de resultados */
        table.results-table { border: 1px solid #107c10; margin-top: 20px; }
        .results-table th { background-color: #e0f0e0; color: #107c10; }
        .result-row td { background-color: #fffac8; color: #333; }
        
        /* Estilos generales */
        input[type="number"], 
        input[type="text"] { /* Incluir type='text' para el nombre de la tabla */
            width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; text-align: center; 
        }
        label { display: block; margin-top: 10px; font-weight: bold; }
        button { background-color: #107c10; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-top: 15px; display: block; width: 100%; }
        button:hover { background-color: #0d680d; }
        .error { margin-top: 10px; padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <h2> Calculadora de Estimacion Puntual</h2>
    
    <?php 
    if ($error) {
        echo "<div class='error'><strong>¡Error!</strong> " . htmlspecialchars($error) . "</div>";
    }

    // === VISTA 3: Mostrar Resultados ===
    if ($media !== null) {
        $num_datos = count($numeros_ingresados);
        ?>
        <hr>
        <h3>Resultados de la Estimación Puntual para: <?php echo $nombre_tabla; ?></h3>
        <table class="excel-grid results-table">
            <thead><tr><th colspan="2">Resumen de la Media</th></tr></thead>
            <tbody>
                <tr><td style="text-align: center;">Nombre de la Tabla</td><td><?php echo $nombre_tabla; ?></td></tr>
                <tr><td style="text-align: center;">Número de datos</td><td><?php echo $num_datos; ?></td></tr>
                <tr><td style="text-align: center;">Suma Total</td><td><?php echo number_format(array_sum($numeros_ingresados), 2); ?></td></tr>
                <tr class="result-row">
                    <td style="text-align: center;">Estimación Puntual</td>
                    <td style="text-align: right; background-color: #e9ffdf;">
                        <?php echo number_format($media, 4); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <form method="GET" action="index.php">
  <button type="submit" style="background-color: #6c757d;">Iniciar Nuevo Cálculo</button>
</form>

    <?php
    // === VISTA 2: Mostrar Tabla de Entrada de Datos Dinámica ===
    } elseif ($datos_max > 0) {
        ?>
        <hr>
        
        <p>A continuación, ingrese los <?php echo $datos_max; ?> valores. Deje vacías las celdas sin datos:</p>
        <form method="POST" action="">
            <input type="hidden" name="datos_max_hidden" value="<?php echo $datos_max; ?>">
            <input type="hidden" name="nombre_tabla_hidden" value="<?php echo $nombre_tabla; ?>"> <input type="hidden" name="calcular_media" value="1">
            
            <table class="excel-grid">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th> <th><?php echo $nombre_tabla; ?></th> </tr>
                </thead>
                <tbody>
                    <?php
                    // Generar las filas dinámicamente
                    for ($i = 1; $i <= $datos_max; $i++) {
                        // Mantener el valor ingresado si el formulario falla y vuelve
                        $valor_anterior = $_POST['dato_' . $i] ?? '';
                        
                        echo "<tr>";
                        echo "<th>$i</th>"; // Encabezado de Fila (Número)
                        echo "<td>";
                        // type='number' y step='any' para permitir decimales
                        echo "<input type='number' step='any' name='dato_" . $i . "' value='" . htmlspecialchars($valor_anterior) . "' placeholder='Dato'>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <button type="submit">Calcular Media</button>
        </form>
        
    <?php
    // === VISTA 1: Pedir la Cantidad de Datos ===
    } else {
        ?>
        <p>Primero, introduce la cantidad de datos y, opcionalmente, un nombre para la tabla:</p>
        <form method="POST" action="">
            
            <label for="nombre_tabla">Nombre de la Tabla (Opcional):</label>
            <input type="text" id="nombre_tabla" name="nombre_tabla" maxlength="50" placeholder="Ej: Puntajes de Examen">
            
            <label for="num_datos">Cantidad de datos (Máx. 50):</label>
            <input type="number" id="num_datos" name="num_datos" min="1" max="50" required placeholder="Ej: 8">
            
            <button type="submit" style="background-color: #0078d4;">Crear Tabla</button>
        </form>
        <?php
    }
    ?>

</div>

</body>
</html>