<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotador con Control Manual</title>
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
        const vistas = ['TVsemanaActual.php', 'TVsemanaProx.php'];
        let indiceActual = 0;
        const frame = document.getElementById('contenedor-vista');
        const TIEMPO_MS = 5 * 60 * 1000; // 5 minutos
        let temporizador;

        function cambiarVista() {
            frame.src = vistas[indiceActual];
            indiceActual = (indiceActual + 1) % vistas.length;
            
            // Reiniciar el temporizador cada vez que cambia la vista
            reiniciarTemporizador();
        }

        function reiniciarTemporizador() {
            clearInterval(temporizador);
            temporizador = setInterval(cambiarVista, TIEMPO_MS);
        }

        function forzarCambio() {
            // Al hacer clic, cambiamos manualmente
            cambiarVista();
            //console.log("Cambio manual realizado. Cronómetro reiniciado.");
        }

        // Carga inicial
        cambiarVista();
    </script>

</body>
</html>