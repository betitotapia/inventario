<?php
 require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_almacen'];
    $id_usuario=$_POST['id_usuario'];

    $sql = "UPDATE usuarios SET almacen = :id_almacen WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_almacen', $id);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    // if ($stmt->execute()) {
    //     echo  "<script> alert('✅ Almacén actualizado correctamente.');</script>";
    // } else {
    //     echo "<script> alert('❌ Error al actualizar el almacén.');</script>";
    // }
}
?>
