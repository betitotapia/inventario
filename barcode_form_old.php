<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Generación de Etiquetas</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .etiqueta {
            margin-top: 30px;
            padding: 20px;
            border: 1px dashed #ccc;
            text-align: center;
            display: none;
        }
        .etiqueta h2 {
            margin-top: 0;
        }
        #barcode {
            margin: 20px 0;
        }
        .busqueda {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .reimprimir-btn {
            background-color: #2196F3;
            padding: 5px 10px;
            font-size: 14px;
            width: auto;
        }
        .reimprimir-btn:hover {
            background-color: #0b7dda;
        }
        /* Estilos para impresión */
@media print {
    body * {
        visibility: hidden;
    }
    .etiqueta, .etiqueta * {
        visibility: visible;
    }
    .etiqueta {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none;
        padding: 0;
        margin: 0;
    }
    #imprimir-btn {
        display: none;
    }
}
    </style>
</head>
<body>

    <div class="container">
        <h1>Sistema de Generación de Etiquetas</h1>
        
        <div class="form-group">
            <label for="referencia">Referencia:</label>
            <input type="text" id="referencia" required>
        </div>
        
        <div class="form-group">
            <label for="lote">Lote:</label>
            <input type="text" id="lote" required>
        </div>
        
        <div class="form-group">
            <label for="caducidad">Fecha de Caducidad:</label>
            <input type="date" id="caducidad" required>
        </div>
        
        <button id="generar-btn">Generar Etiqueta</button>
        
        <div class="etiqueta" id="etiqueta-container">
            <h2 id="etiqueta-titulo"></h2>
            <svg id="barcode"></svg>
            <p id="etiqueta-info"></p>
            <button id="imprimir-btn">Imprimir Etiqueta</button>
        </div>
        
        <div class="busqueda">
            <h2>Buscar Etiquetas</h2>
            <div class="form-group">
                <label for="busqueda">Buscar (referencia, lote o código):</label>
                <input type="text" id="busqueda">
            </div>
            <button id="buscar-btn">Buscar</button>
            
            <div id="resultados">
                <table id="resultados-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Referencia</th>
                            <th>Lote</th>
                            <th>Caducidad</th>
                            <th>Código Barras</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generar nueva etiqueta
            document.getElementById('generar-btn').addEventListener('click', generarEtiqueta);
            
            // Buscar etiquetas existentes
            document.getElementById('buscar-btn').addEventListener('click', buscarEtiquetas);
            
            // Imprimir etiqueta
            document.getElementById('imprimir-btn').addEventListener('click', imprimirEtiqueta);
          
            // Configurar fecha actual como valor por defecto
            const today = new Date();
            const formattedDate = today.toISOString().substr(0, 10);
            document.getElementById('caducidad').value = formattedDate;
        });
        
        function generarEtiqueta() {
            const referencia = document.getElementById('referencia').value.trim();
            const lote = document.getElementById('lote').value.trim();
            const caducidad = document.getElementById('caducidad').value.trim();
            
            if (!referencia || !lote || !caducidad) {
                alert('Por favor complete todos los campos');
                return;
            }
            
            const data = new FormData();
            data.append('action', 'generar');
            data.append('referencia', referencia);
            data.append('lote', lote);
            data.append('caducidad', caducidad);
            
            fetch('backend.php', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                mostrarEtiqueta(data.referencia, data.codigo_barras, data.id);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al generar la etiqueta');
            });
        }
       // Reemplaza TODO el contenido del script desde la primera función mostrarEtiqueta hasta el final con esto:

function mostrarEtiqueta(referencia, codigoBarras, id) {
    const etiquetaContainer = document.getElementById('etiqueta-container');
    etiquetaContainer.innerHTML = `
        <h2 id="etiqueta-titulo">${referencia}</h2>
        <svg id="barcode"></svg>
        <p id="etiqueta-info">Código: ${codigoBarras} | ID: ${id}</p>
        <button id="imprimir-btn">Imprimir Etiqueta</button>
    `;
    
    // Generar código de barras
    JsBarcode("#barcode", codigoBarras, {
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 50,
        margin: 10,
        displayValue: true
    });
    
    // Agregar evento de impresión
    const imprimirBtn = document.getElementById('imprimir-btn');
    imprimirBtn.addEventListener('click', imprimirEtiqueta);
    
    etiquetaContainer.style.display = 'block';
}

function imprimirEtiqueta() {
    const etiquetaContainer = document.getElementById('etiqueta-container');
    const referencia = document.getElementById('etiqueta-titulo').textContent;
    const info = document.getElementById('etiqueta-info').textContent;
    //const codigoBarras = info.match(/Código: (\d+)/)[1];
    const codigoBarras = info.match(/Código: (.+?) \|/)[1];
    console.log(codigoBarras);
    // Crear ventana de impresión
    const ventanaImpresion = window.open('', '_blank');
    ventanaImpresion.document.open();
    ventanaImpresion.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Etiqueta ${referencia}</title>
            <style>
                @page { 
                    size: auto; 
                    margin: 0; /* Cambiado a 0 para máximo espacio útil */
                }
                body { 
                    font-family: Arial; 
                    text-align: center; 
                    margin: 10mm; /* Margen ahora en el body */
                    padding: 0;
                }
                h2 { 
                    margin: 0 0 5mm 0; 
                    font-size: 24pt; 
                }
                p { 
                    margin: 5mm 0; 
                    font-size: 12pt; 
                }
                svg { 
                    margin: 2mm auto; 
                    display: block;
                    max-width: 100%;
                    height: auto;
                }
            </style>
        </head>
        <body>
            <h2>${referencia}</h2>
            <svg id="barcode-print" width="100%" height="50"></svg>
            <p>${info}</p>
            <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
            <script>
                window.onload = function() {
                    JsBarcode("#barcode-print", "${codigoBarras}", {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 2,
                        height: 50,  /* Asegurar misma altura que en vista */
                        displayValue: true,
                        margin: 10   /* Añadir margen alrededor del código */
                    });
                    setTimeout(function() {
                        window.print();
                        window.close();
                    }, 500);
                }
            <\/script>
        </body>
        </html>
    `);
    ventanaImpresion.document.close();
}

function buscarEtiquetas() {
            const query = document.getElementById('busqueda').value.trim();
            
            if (!query) {
                alert('Por favor ingrese un término de búsqueda');
                return;
            }
            
            const data = new FormData();
            data.append('action', 'buscar');
            data.append('query', query);
            
            fetch('backend.php', {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                const tbody = document.querySelector('#resultados-table tbody');
                tbody.innerHTML = '';
                
                if (data.etiquetas.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = '<td colspan="6">No se encontraron resultados</td>';
                    tbody.appendChild(row);
                    return;
                }
                
                data.etiquetas.forEach(etiqueta => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${etiqueta.id}</td>
                        <td>${etiqueta.referencia}</td>
                        <td>${etiqueta.lote}</td>
                        <td>${etiqueta.caducidad}</td>
                        <td>${etiqueta.codigo_barras}</td>
                        <td><button class="reimprimir-btn" data-id="${etiqueta.id}">Reimprimir</button></td>
                    `;
                    tbody.appendChild(row);
                });
                
                // Agregar eventos a los botones de reimpresión
                document.querySelectorAll('.reimprimir-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        reimprimirEtiqueta(this.getAttribute('data-id'));
                    });
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al buscar etiquetas');
            });
        }
        



function reimprimirEtiqueta(id) {
    const data = new FormData();
    data.append('action', 'reimprimir');
    data.append('id', id);
    
    fetch('backend.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        
        mostrarEtiqueta(data.referencia, data.codigo_barras, data.id);
        window.scrollTo(0, 0);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al cargar la etiqueta');
    });
}
</script>
</body>
</html>