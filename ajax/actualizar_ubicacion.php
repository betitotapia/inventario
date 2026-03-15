<?php
header('Content-Type: application/json');
 require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';


$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$ubicacion = isset($_POST['ubicacion']) ? $_POST['ubicacion'] : '';

try {
    $stmt = $pdo->prepare("UPDATE productos SET ubicacion = ? WHERE id = ?");
    $stmt->execute([$ubicacion, $id]);
    
    echo $id. " " . $ubicacion;
    //json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>