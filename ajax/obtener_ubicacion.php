<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

// $data = json_decode(file_get_contents('php://input'), true);
// $id = $data['id'];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['error' => 'ID no válido']);
    exit;
}



try {
    $stmt = $pdo->prepare("SELECT ubicacion FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['ubicacion' => $result['ubicacion']]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener la ubicación: ' . $e->getMessage()]);
}
// Asegúrate de reemplazar 'tu_tabla' con el nombre real de tu tabla
// y 'ubicacion' con el nombre real de la columna que almacena la ubicación.    
?>