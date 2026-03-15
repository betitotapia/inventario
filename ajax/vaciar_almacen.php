<?php
   require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 

    $id_almacen = $_POST['id_almacen'];

    $sql = "UPDATE productos SET cantidad = 0 WHERE almacen = :id_almacen";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_almacen', $id_almacen);
    if ($stmt->execute()) {
        echo "<script> alert('✅ Almacén A cero correctamente.');</script>";
    } else {
        echo "<script> alert('❌ Error al vaciar el almacén.');</script>";
    }
}