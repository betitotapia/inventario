<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $id_almacen = $_POST['id_almacen'];
    $tipo_usuario = (int)$_POST['tipo_usuario'];

    if (!$nombre || !$username || !$password || !$id_almacen || !$tipo_usuario) {
        die("Todos los campos son obligatorios.");
    }

    if (strlen($password) < 6) {
        die("La contraseña debe tener al menos 6 caracteres.");
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si el username ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        die("El nombre de usuario ya está registrado.");
    }

    // Insertar nuevo usuario
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (username, password,nombre,almacen,is_admin)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $hash, $nombre, $id_almacen, $tipo_usuario]);

    echo "<script>alert('Usuario registrado con éxito'); window.location='usuarios.php';</script>";
}
