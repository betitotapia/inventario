<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

header('Content-Type: application/json; charset=utf-8');

$almacen = isset($_GET['almacen_origen']) ? (int)$_GET['almacen_origen'] : 0;
$codigo  = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';
$lote    = isset($_GET['lote']) ? trim($_GET['lote']) : '';

if ($almacen <= 0 || $codigo === '') {
    echo json_encode([]);
    exit;
}

// Normalizaciones útiles
$codigo16 = substr($codigo, 0, 16);
$codigo19 = substr($codigo, 0, 19);

// Construimos una búsqueda tolerante:
// - match exacto por codigo
// - match por prefijo (LEFT 16) para códigos GS1 largos
// - match por prefijo 19 (etiqueta SUMED 113+ref16)
// - opcionalmente match por GTIN14 si viene como 01+GTIN y en BD guardaron sólo GTIN
$gtin14 = null;
if (strpos($codigo16, '01') === 0 && strlen($codigo16) >= 16) {
    $maybe = substr($codigo16, 2, 14);
    if (preg_match('/^\d{14}$/', $maybe)) {
        $gtin14 = $maybe;
    }
}

$sql = "SELECT id, codigo, lote, caducidad, referencia, cantidad, almacen, ubicacion
        FROM productos
        WHERE almacen = :almacen
          AND cantidad > 0
          AND (
                codigo = :codigo
             OR LEFT(codigo, 16) = LEFT(:codigo16, 16)
             OR LEFT(codigo, 19) = LEFT(:codigo19, 19)";

$params = [
    ':almacen'  => $almacen,
    ':codigo'   => $codigo,
    ':codigo16' => $codigo16,
    ':codigo19' => $codigo19,
];

if ($gtin14 !== null) {
    $sql .= " OR codigo = :gtin14";
    $params[':gtin14'] = $gtin14;
}

$sql .= ")";

if ($lote !== '') {
    $sql .= " AND lote = :lote";
    $params[':lote'] = $lote;
}

$sql .= " ORDER BY referencia, lote LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
