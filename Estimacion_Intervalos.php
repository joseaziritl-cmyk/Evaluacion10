<?php
// Inicializar variables
$datos_max = 0;
$nombre_tabla = '';
$media = null;
$error = '';
$numeros_ingresados = [];
$nivel_confianza = 0.95; // Valor por defecto
$desv_estandar = null;
$margen_error = null;
$intervalo_inferior = null;
$intervalo_superior = null;

// Valores Z (Críticos) para los niveles de confianza comunes (Distribución Normal Estándar)
$valores_z = [
    '0.90' => 1.645,
    '0.95' => 1.960, // El más común
    '0.99' => 2.576
];

// === FUNCIONES ESTADÍSTICAS ADICIONALES ===

/**
 * Calcula la Desviación Estándar Muestral (s).
 * Usa n-1 en el denominador (Bessel's correction).
 * @param array $datos Arreglo de números.
 * @param float $media Media de los datos.
 * @return float|null Desviación estándar o null si no hay suficientes datos.
 */
function calcular_desviacion_estandar_muestral($datos, $media) {
    $n = count($datos);
    if ($n < 2) {
        return null;
    }
    
    $sum_cuadrados = 0;
    foreach ($datos as $x) {
        $sum_cuadrados += pow($x - $media, 2);
    }
    
    // Fórmula de la Desviación Estándar Muestral
    return sqrt($sum_cuadrados / ($n - 1));
}


// === PROCESAMIENTO DE ESTADOS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Si se envía el número de datos, el nombre y el nivel de confianza (Formulario #1)
    if (isset($_POST['num_datos'])) {
        $num = (int)$_POST['num_datos'];
        $nombre_tabla_temp = trim($_POST['nombre_tabla'] ?? '');
        $nivel_confianza_temp = $_POST['nivel_confianza'] ?? $nivel_confianza;
        
        if ($num > 0 && $num <= 50) {
            $datos_max = $num;
            $nombre_tabla = !empty($nombre_tabla_temp) ? htmlspecialchars($nombre_tabla_temp) : 'Datos de Muestra';
            $nivel_confianza = (float)$nivel_confianza_temp; // Asignar el nivel de confianza
        } else {
            $error = "Por favor, introduce un número entre 1 y 50.";
        }
        
    }
    
    // Si se envían los datos para calcular (Formulario #2)
    elseif (isset($_POST['calcular_intervalo']) && isset($_POST['datos_max_hidden'])) {
        $datos_max = (int)$_POST['datos_max_hidden'];
        $nombre_tabla = htmlspecialchars($_POST['nombre_tabla_hidden'] ?? 'Datos de Muestra');
        $nivel_confianza = (float)$_POST['nivel_confianza_hidden']; // Recuperar Nivel de Confianza
        
        for ($i = 1; $i <= $datos_max; $i++) {
            $key = 'dato_' . $i;
            $valor = $_POST[$key] ?? '';
            
            if (is_numeric($valor) && trim($valor) !== '') {
                $numeros_ingresados[] = (float)$valor;
            }
        }

        // --- CÁLCULO DEL INTERVALO DE CONFIANZA ---
        $conteo = count($numeros_ingresados);

        if ($conteo > 1) { // Se necesitan al menos 2 datos para la desviación muestral
            $suma = array_sum($numeros_ingresados);
            $media = $suma / $conteo;
            
            $desv_estandar = calcular_desviacion_estandar_muestral($numeros_ingresados, $media);
            
            // 1. Obtener el Valor Crítico Z
            $z_critico = $valores_z[(string)$nivel_confianza] ?? $valores_z['0.95']; // Usar 1.96 si falla

            // 2. Calcular el Error Estándar de la Media
            $error_estandar = $desv_estandar / sqrt($conteo);

            // 3. Calcular el Margen de Error
            $margen_error = $z_critico * $error_estandar;
            
            // 4. Calcular los límites del Intervalo de Confianza
            $intervalo_inferior = $media - $margen_error;
            $intervalo_superior = $media + $margen_error;
            
        } else {
            $error = "Se necesitan al menos dos datos válidos para calcular el Intervalo de Confianza. Inténtalo de nuevo.";
            $datos_max = 0; // Volver al estado inicial
            $nombre_tabla = '';
        }
    }
}
?>

<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Hoja de Cálculo de Intervalo de Confianza</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; background-color: #f0f0f0; }
        .container { max-width: 650px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #107c10; text-align: center; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        
        /* Estilos de la tabla de entrada (Estilo Excel) */
        table.excel-grid { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        .excel-grid th, .excel-grid td { border: 1px solid #c8c8c8; padding: 0; height: 25px; }
        .excel-grid thead th { background-color: #e6e6e6; color: #333; font-weight: bold; border-color: #999; text-align: center; }
        .excel-grid tbody th { width: 40px; background-color: #e6e6e6; color: #333; font-weight: bold; text-align: center; border-color: #999; }
        .excel-grid td { text-align: right; background-color: #ffffff; }
        .excel-grid input[type="number"] {
            width: 100%; height: 25px; padding: 0 5px;
            border: none; text-align: right; box-sizing: border-box;
            font-family: inherit; font-size: 14px;
        }
        .excel-grid input[type="number"]:focus { outline: 2px solid #8a2be2; box-shadow: 0 0 5px rgba(138, 43, 226, 0.5); }

        /* Estilos de la tabla de resultados */
        table.results-table { border: 1px solid #8a2be2; margin-top: 20px; }
        .results-table th { background-color: #f0e6ff; color: #8a2be2; }
        .result-row td { background-color: #e6ccff; color: #333; font-weight: bold; }
        
        /* Estilos generales */
        input[type="number"],
        input[type="text"],
        select { /* Añadir select para el nivel de confianza */
            width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; text-align: center;
        }
        label { display: block; margin-top: 10px; font-weight: bold; }
        button { background-color: #107c10; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-top: 15px; display: block; width: 100%; }
        button:hover { background-color: #107c10; }
        .error { margin-top: 10px; padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; }

        /* Estilos para el nuevo campo de ayuda */
        .confianza-group { 
            display: flex; 
            align-items: flex-end; /* Alinea los elementos al final */
            gap: 10px; /* Espacio entre el select y el botón de ayuda */
            margin-top: 5px;
        }
        .confianza-group select {
            flex-grow: 1; /* Permite que el select crezca */
            width: auto; /* Anula el 100% de arriba si es necesario */
        }
        .help-button {
            background-color: #0078d4;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            width: 40px; /* Tamaño fijo para el botón */
            height: 40px; /* Tamaño fijo para el botón */
            margin-top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            line-height: 1;
        }
        .help-button:hover {
            background-color: #005a9e;
        }

        /* Estilo para la ventana emergente (modal/popup simple) */
        .popup {
            display: none; /* Oculto por defecto */
            position: fixed; /* Fijo en la pantalla */
            z-index: 10; /* Encima de todo */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4); /* Fondo semi-transparente */
        }
        .popup-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% superior y centrado horizontalmente */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Ancho del modal */
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
        }
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .popup-content h4 {
            color: #107c10;
            margin-top: 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2> Calculadora de Estimación en Intervalos </h2>
    
    <?php
    if ($error) {
        echo "<div class='error'><strong>¡Error!</strong> " . htmlspecialchars($error) . "</div>";
    }

    // === VISTA 3: Mostrar Resultados ===
    if ($intervalo_inferior !== null) {
        $num_datos = count($numeros_ingresados);
        $confianza_porcentaje = $nivel_confianza * 100;
        ?>
        <hr>
        <h3>Intervalo de Confianza del <?php echo $confianza_porcentaje; ?>% para: <?php echo $nombre_tabla; ?></h3>
        <table class="excel-grid results-table">
            <thead><tr><th colspan="2">Resumen del Intervalo</th></tr></thead>
            <tbody>
                <tr><td style="text-align: center;">Nivel de Confianza</td><td><?php echo $confianza_porcentaje; ?>%</td></tr>
                <tr><td style="text-align: center;">Tamaño de la Muestra</td><td><?php echo $num_datos; ?></td></tr>
                <tr><td style="text-align: center;">Media Muestral </td><td><?php echo number_format($media, 4); ?></td></tr>
                <tr><td style="text-align: center;">Desviación Estándar</td><td><?php echo number_format($desv_estandar, 4); ?></td></tr>
                <tr><td style="text-align: center;">Valor Crítico</td><td><?php echo number_format($valores_z[(string)$nivel_confianza] ?? $valores_z['0.95'], 3); ?></td></tr>
                <tr><td style="text-align: center;">Margen de Error</td><td><?php echo number_format($margen_error, 4); ?></td></tr>
                
                <tr class="result-row">
                    <td style="text-align: center;">Límite Inferior</td>
                    <td style="text-align: right; background-color: #e9dfff; color: #333;">
                        <?php echo number_format($intervalo_inferior, 4); ?>
                    </td>
                </tr>
                <tr class="result-row">
                    <td style="text-align: center;">Límite Superior</td>
                    <td style="text-align: right; background-color: #e9dfff; color: #333;">
                        <?php echo number_format($intervalo_superior, 4); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center; font-weight: bold; background-color: #fff; padding: 10px;">
                        El Intervalo de Confianza es: [<?php echo number_format($intervalo_inferior, 4); ?> ; <?php echo number_format($intervalo_superior, 4); ?>]
                    </td>
                </tr>
            </tbody>
        </table>
        <form method="GET" action="">
            <button type="submit" style="background-color: #6c757d;">Iniciar Nuevo Cálculo</button>
        </form>
    <?php
    // === VISTA 2: Mostrar Tabla de Entrada de Datos Dinámica ===
    } elseif ($datos_max > 0) {
        ?>
        <hr>
        
        <p>A continuación, ingrese los <?php echo $datos_max; ?> valores para calcular el intervalo de confianza del <?php echo ($nivel_confianza * 100); ?>%.</p>
        <form method="POST" action="">
            <input type="hidden" name="datos_max_hidden" value="<?php echo $datos_max; ?>">
            <input type="hidden" name="nombre_tabla_hidden" value="<?php echo $nombre_tabla; ?>">
            <input type="hidden" name="nivel_confianza_hidden" value="<?php echo $nivel_confianza; ?>">
            <input type="hidden" name="calcular_intervalo" value="1">
            
            <table class="excel-grid">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th> <th><?php echo $nombre_tabla; ?></th> </tr>
                </thead>
                <tbody>
                    <?php
                    // Generar las filas dinámicamente
                    for ($i = 1; $i <= $datos_max; $i++) {
                        $valor_anterior = $_POST['dato_' . $i] ?? '';
                        
                        echo "<tr>";
                        echo "<th>$i</th>";
                        echo "<td>";
                        echo "<input type='number' step='any' name='dato_" . $i . "' value='" . htmlspecialchars($valor_anterior) . "' placeholder='Dato $i'>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <button type="submit">Calcular Intervalo de Confianza</button>
        </form>
        
    <?php
    // === VISTA 1: Pedir la Cantidad de Datos y Nivel de Confianza ===
    } else {
        ?>
        <p>Primero, introduce la cantidad de datos y el nivel de confianza deseado:</p>
        <form method="POST" action="">
            
            <label for="nombre_tabla">Nombre de la Tabla (Opcional):</label>
            <input type="text" id="nombre_tabla" name="nombre_tabla" maxlength="50" placeholder="Ej: Estaturas">

            <label for="nivel_confianza">Nivel de Confianza:</label>
            <div class="confianza-group">
                <select id="nivel_confianza" name="nivel_confianza" required>
                    <option value="0.95" selected>95%</option>
                    <option value="0.90">90%</option>
                    <option value="0.99">99%</option>
                </select>
                <button type="button" class="help-button" onclick="document.getElementById('confianza-popup').style.display='block'">
                    ?
                </button>
            </div>
            
            <label for="num_datos">Cantidad de datos (Mín. 2, Máx. 50):</label>
            <input type="number" id="num_datos" name="num_datos" min="2" max="50" required placeholder="Ej: 15">
            
            <button type="submit" style="background-color: #0078d4;">Crear Tabla de Datos</button>
        </form>
        <?php
    }
    ?>

</div>

<div id="confianza-popup" class="popup">
    <div class="popup-content">
        <span class="close-button" onclick="document.getElementById('confianza-popup').style.display='none'">&times;</span>
        <h4>¿Cómo Elegir el Nivel de Confianza Adecuado?</h4>
        <p>El <strong>Nivel de Confianza </strong>representa la probabilidad de que el verdadero valor del parámetro poblacional (en este caso, la media) se encuentre dentro del intervalo calculado.</p>
        <ul>
            <li><strong>Mayor Confianza = Mayor Amplitud:</strong> Un nivel de confianza más alto (ej. 99%) resultará en un intervalo más amplio (mayor margen de error), lo que aumenta la certeza, pero hace la estimación menos precisa.</li>
            <li><strong>Menor Confianza = Menor Amplitud:</strong> Un nivel de confianza más bajo (ej. 90%) resulta en un intervalo más estrecho (menor margen de error), lo que aumenta la precisión, pero disminuye la certeza.</li>
            <li><strong>El 95% es el estándar:</strong> En la mayoría de las investigaciones sociales y científicas, el 95% es el nivel de confianza más utilizado, ya que ofrece un buen equilibrio entre certeza y precisión.</li>
        </ul>
    <p>
        

    </div>
</div>

<script>
    // Cerrar el popup al hacer clic fuera de él
    window.onclick = function(event) {
        var modal = document.getElementById('confianza-popup');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>