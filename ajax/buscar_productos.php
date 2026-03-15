<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

header('Content-Type: application/json; charset=utf-8');

$referencia = isset($_GET['referencia']) ? trim($_GET['referencia']) : '';
$lote       = isset($_GET['lote']) ? trim($_GET['lote']) : '';
$almacen    = isset($_GET['almacen_origen']) ? (int)$_GET['almacen_origen'] : 0;

if ($almacen <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, codigo, lote, caducidad, referencia, cantidad, almacen, ubicacion
        FROM productos
        WHERE almacen = :almacen
          AND cantidad > 0";

$params = [':almacen' => $almacen];

if ($referencia !== '') {
    $sql .= " AND referencia LIKE :referencia";
    $params[':referencia'] = "%{$referencia}%";
}
if ($lote !== '') {
    $sql .= " AND lote LIKE :lote";
    $params[':lote'] = "%{$lote}%";
}

$sql .= " ORDER BY referencia, lote LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($productos);
