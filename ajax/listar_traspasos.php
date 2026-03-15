<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

header('Content-Type: application/json; charset=utf-8');

$id_usuario = $_SESSION['user_id'] ?? 0;

// Permisos por almacén (mismo patrón que detalle_almacen.php)
$usuario_almacen = $pdo->query(
    "SELECT almacen, is_admin 
     FROM usuarios
     WHERE id_usuario = " . (int)$id_usuario
)->fetch(PDO::FETCH_ASSOC);

$id_almacen_usuario = $usuario_almacen['almacen'] ?? null;
$is_admin           = (int)($usuario_almacen['is_admin'] ?? 0);

$fecha_desde    = $_GET['fecha_desde']    ?? '';
$fecha_hasta    = $_GET['fecha_hasta']    ?? '';
$almacen_origen = (int)($_GET['almacen_origen'] ?? 0);
$almacen_dest   = (int)($_GET['almacen_destino'] ?? 0);
$folio          = trim($_GET['folio'] ?? '');

$sql = "SELECT te.id, te.fecha, te.folio,
               te.almacen_origen, te.almacen_destino,
               te.id_usuario
        FROM traspasos_encabezado te
        WHERE 1=1";

$params = [];

// Filtro por rango de fechas
if ($fecha_desde !== '') {
    $sql .= " AND te.fecha >= :fdesde";
    $params[':fdesde'] = $fecha_desde;
}
if ($fecha_hasta !== '') {
    $sql .= " AND te.fecha <= :fhasta";
    $params[':fhasta'] = $fecha_hasta;
}

// Filtros opcionales de almacén
if ($almacen_origen > 0) {
    $sql .= " AND te.almacen_origen = :ao";
    $params[':ao'] = $almacen_origen;
}
if ($almacen_dest > 0) {
    $sql .= " AND te.almacen_destino = :ad";
    $params[':ad'] = $almacen_dest;
}

// Filtro por folio
if ($folio !== '') {
    $sql .= " AND te.folio LIKE :folio";
    $params[':folio'] = "%{$folio}%";
}

// Permiso de usuario: si no es admin 1/2/6, solo lo que involucre su almacén
if (!in_array($is_admin, [1, 2, 6], true) && $id_almacen_usuario !== null) {
    $sql .= " AND (te.almacen_origen = :almuser OR te.almacen_destino = :almuser)";
    $params[':almuser'] = $id_almacen_usuario;
}

$sql .= " ORDER BY te.fecha DESC, te.id DESC LIMIT 500";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si tienes una tabla de usuarios, aquí podrías traducir id_usuario a nombre
// de momento devolvemos solo el id
foreach ($rows as &$r) {
    $r['usuario'] = $r['id_usuario']; // ajústalo si quieres mostrar nombre
}

echo json_encode($rows);
