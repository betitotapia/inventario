<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';// Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

$id_usuario = $_SESSION['user_id'];
$datos_usuario = $pdo->query("SELECT * FROM usuarios WHERE id_usuario = ".$id_usuario)->fetch(PDO::FETCH_ASSOC);
$almacen = $datos_usuario['almacen'];

echo json_encode($pdo->query("SELECT * FROM productos WHERE almacen= ".$almacen." ORDER BY ultima_modificacion DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC)); ?>