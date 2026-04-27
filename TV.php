<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planeación Semanal TV</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }
        #contenedor-vista {
            width: 100%;
            height: 100vh;
            border: none;
        }
        /* Estilo para el botón flotante */
        #btn-siguiente {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: background 0.3s;
        }
        #btn-siguiente:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <button id="btn-siguiente" onclick="forzarCambio()">Siguiente Vista ➔</button>
    
    <iframe id="contenedor-vista" src=""></iframe>

<script>
        // 1. Definición de constantes de URL
        const CALIBRACIONES = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=f996192e989837518296';
        const FUERZA = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=d68dde29c39802b670ac';
        const DUREZA = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=8224cf508598598e365c';
        const DIMENSIONAL = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=e437fb94038cce035c52';
        const ELECTRICA = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=1f72c63898a538ea9730';
        const HUMEDAD = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=ed220bb0c5debabb0aee';
        const MASA = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=fbee9898942972e00598';
        const PAR_TORSIONAL = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=60273e02d7e4a0690d31';
        const PRESION = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=17ec3680392880511306';
        const TEMPERATURA = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=a84c5c639478bac185c9';
        const TIEMPO_FRECUENCIA = 'https://app.powerbi.com/view?r=eyJrIjoiMGQ1YjcyMjAtZDU2Yi00MDExLWE2ZWUtYmI1N2FlYzk3ODgwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=54601dbb09210c071d03';

        // 2. Configuración del Arreglo y Variables de Control
        const vistas = [
            'TVsemanaActual.php', 
            'TVsemanaProx.php', 
            CALIBRACIONES, FUERZA, DUREZA, DIMENSIONAL, 
            ELECTRICA, HUMEDAD, MASA, PAR_TORSIONAL, 
            PRESION, TEMPERATURA, TIEMPO_FRECUENCIA
        ];

        let indiceActual = 0;
        const frame = document.getElementById('contenedor-vista');
        const TIEMPO_MS = 1 * 60 * 1000; // 1 minuto
        let temporizador;

        // 3. Funciones de Lógica
        function cambiarVista() {
            // Asignar la URL actual al iframe
            frame.src = vistas[indiceActual];
            
            // Log de control (opcional)
            console.log("Cargando vista " + (indiceActual + 1) + ": " + vistas[indiceActual]);

            // Preparar el siguiente índice (vuelve a 0 al llegar al final)
            indiceActual = (indiceActual + 1) % vistas.length;
            
            // Programar el siguiente cambio automático
            reiniciarTemporizador();
        }

        function reiniciarTemporizador() {
            // Limpia cualquier conteo previo para evitar que se encimen
            clearTimeout(temporizador);
            // Inicia un nuevo conteo
            temporizador = setTimeout(cambiarVista, TIEMPO_MS);
        }

        function forzarCambio() {
            // Al hacer clic manual, ejecuta el cambio inmediatamente
            cambiarVista();
        }

        // 4. Inicio automático al cargar la página
        window.onload = cambiarVista;
        
    </script>

</body>
</html>