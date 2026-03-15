<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Subir archivo CSV para SUMAR cantidades de inventario</h2>
    <form action="procesar_csv_suma.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <input type="file" name="archivo_csv" class="form-control" accept=".csv" required>
        </div>
        <button type="submit" class="btn btn-primary">Cargar y Procesar</button>
    </form>
</body>
</html>
