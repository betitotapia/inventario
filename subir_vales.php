<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesamiento de Inventario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
        }
        .upload-form {
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #2980b9;
        }
        #results {
            margin-top: 20px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .success {
            color: green;
        }
        .warning {
            color: orange;
        }
        .error {
            color: red;
        }
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Procesamiento de Inventario</h1>
        
        <div class="upload-form">
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="csvFile">Seleccione archivo CSV:</label>
                    <input type="file" id="csvFile" name="csvFile" accept=".csv" required>
                </div>
                <button type="submit">Procesar Archivo</button>
            </form>
        </div>
        
        <div class="loading" id="loadingIndicator">
            <div class="spinner"></div>
            <p>Procesando archivo, por favor espere...</p>
        </div>
        
        <div id="results"></div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('csvFile');
            const resultsDiv = document.getElementById('results');
            const loadingIndicator = document.getElementById('loadingIndicator');
            
            if (fileInput.files.length === 0) {
                alert('Por favor seleccione un archivo CSV');
                return;
            }
            
            const formData = new FormData();
            formData.append('csvFile', fileInput.files[0]);
            
            // Mostrar indicador de carga
            loadingIndicator.style.display = 'block';
            resultsDiv.innerHTML = '';
            
            fetch('process_vales.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                resultsDiv.innerHTML = data;
                loadingIndicator.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                resultsDiv.innerHTML = '<p class="error">Ocurrió un error al procesar el archivo.</p>';
                loadingIndicator.style.display = 'none';
            });
        });
    </script>
</body>
</html>