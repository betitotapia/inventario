<?php
// Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
$codigo = $_GET['codigo'];
$stmt = $pdo->prepare("SELECT * FROM productos WHERE codigo = ?");
$stmt->execute([$codigo]);
echo $stmt->rowCount() > 0 ? "existe" : "nuevo";
