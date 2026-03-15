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
$p1 = (string)($_POST['password'] ?? '');
$p2 = (string)($_POST['password2'] ?? '');

if ($id_usuario <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Usuario inválido.']);
    exit;
}

if (trim($p1) === '' || strlen($p1) < 6) {
    echo json_encode(['ok' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

if ($p1 !== $p2) {
    echo json_encode(['ok' => false, 'message' => 'Las contraseñas no coinciden.']);
    exit;
}

// Verificar usuario existente
$stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
if (!$stmt->fetchColumn()) {
    echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado.']);
    exit;
}

$hash = password_hash($p1, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
$stmt->execute([$hash, $id_usuario]);

echo json_encode(['ok' => true]);
