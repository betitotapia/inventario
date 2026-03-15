<?php
header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

if (empty($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'No tienes permisos para esta acción.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
$nombre = trim($_POST['nombre'] ?? '');
$username = trim($_POST['username'] ?? '');
$id_almacen = isset($_POST['id_almacen']) ? (int)$_POST['id_almacen'] : 0;
$tipo_usuario = isset($_POST['tipo_usuario']) ? (int)$_POST['tipo_usuario'] : 0;

if ($id_usuario <= 0 || $nombre === '' || $username === '' || $id_almacen <= 0 || $tipo_usuario <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

// Verificar usuario existente
$stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
if (!$stmt->fetchColumn()) {
    echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado.']);
    exit;
}

// Verificar username único (excluyendo al actual)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ? AND id_usuario <> ?");
$stmt->execute([$username, $id_usuario]);
if ((int)$stmt->fetchColumn() > 0) {
    echo json_encode(['ok' => false, 'message' => 'El nombre de usuario ya está en uso.']);
    exit;
}

$stmt = $pdo->prepare(
    "UPDATE usuarios
     SET nombre = ?, username = ?, almacen = ?, is_admin = ?
     WHERE id_usuario = ?"
);
$stmt->execute([$nombre, $username, $id_almacen, $tipo_usuario, $id_usuario]);

echo json_encode(['ok' => true]);
