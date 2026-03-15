<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $existe = $stmt->fetchColumn();

    echo $existe > 0 ? "1" : "0";
}
