<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Selección de Estimación</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px; 
            background-color: #f0f0f0; 
            text-align: center; /* Centrar el contenido general */
        }
        .container { 
            max-width: 800px; /* Ancho un poco mayor para las explicaciones */
            margin: 0 auto; 
            background: #fff; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); /* Sombra más definida */
        }
        h2 { 
            
            text-align: center; 
            border-bottom: 3px solid #e6e6e6; 
            padding-bottom: 15px; 
            margin-bottom: 30px;
        }
        .option-grid {
            display: flex;
            gap: 20px; /* Espacio entre las dos columnas */
            margin-top: 30px;
        }
        .option-card {
            flex: 1;
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(138, 43, 226, 0.2); /* Efecto hover con color púrpura */
        }
        .option-card h3 {
            color: #333;
            margin-top: 0;
            font-size: 1.5em;
        }
        .option-card p {
            font-size: 0.95em;
            color: #555;
            line-height: 1.5;
            min-height: 90px; /* Para que las cajas tengan una altura mínima similar */
        }
        .option-card button { 
            background-color: #107c10; 
            color: white; 
            padding: 12px 25px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            margin-top: 15px; 
            display: block; 
            width: 100%; 
            font-size: 1.1em;
            transition: background-color 0.3s;
        }
        .option-card button:hover { 
            background-color: #107c10; 
        }
    </style>
</head>
<body>

<div class="container">
    <h2> Selecciona el Tipo de Estimación Estadística </h2>
    
    <div class="option-grid">
        
        <div class="option-card">
            <h3>Estimación por Intervalos</h3>
            <p>
                Calcula un rango de valores (intervalo) dentro del cual es probable que se encuentre el parámetro poblacional verdadero (la media, por ejemplo). 
                Se basa en un nivel de confianza (90%, 95%, etc.).
            </p>
            <form action="Estimacion_Intervalos.php" method="GET">
                <button type="submit">Calcular Intervalo de Confianza</button>
            </form>
        </div>
        
        <div class="option-card">
            <h3>Estimación Puntual</h3>
            <p>
                Calcula un único valor que sirve como la el estimador del parámetro poblacional. 
                El ejemplo más común es la media muestral como estimación de la media poblacional.
            </p>
            <form action="Estimacion_Puntual.php" method="GET">
                <button type="submit">Calcular Estimación Puntual</button>
            </form>
        </div>
        
    </div>

</div>

</body>
</html>