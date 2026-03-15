<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

?>

<!DOCTYPE html>
<html lang="es">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/head.php'; ?>
<body>
   <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/navbar.php'; ?> 
<body class="container mt-5">
    <h2>Subir archivo CSV para restar cantidades de inventario</h2>
    <form action="procesar_csv.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <input type="file" name="archivo_csv" class="form-control" accept=".csv" required>
        </div>
        <button type="submit" class="btn btn-primary">Cargar y Procesar</button>
    </form>
</body>
</html>
