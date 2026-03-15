<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_almacen_origen = $_POST['id_almacen_origen'];
    $id_almacen_destino = 1; // Almacén destino fijo

    $sql = "UPDATE productos SET almacen = :id_almacen_destino WHERE almacen = :id_almacen_origen";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_almacen_destino', $id_almacen_destino);
    $stmt->bindParam(':id_almacen_origen', $id_almacen_origen);
    if ($stmt->execute()) {
        echo "<script> alert('✅ Traspaso de almacén realizado correctamente.');</script>";
    } else {
        echo "<script> alert('❌ Error al traspasar el almacén.');</script>";
    }
}