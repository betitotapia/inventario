<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

header('Content-Type: application/json; charset=utf-8');

$id_traspaso = (int)($_GET['id_traspaso'] ?? 0);

if ($id_traspaso <= 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'ID de traspaso inválido']);
    exit;
}

// Obtenemos encabezado
$stmt = $pdo->prepare(
    "SELECT * FROM traspasos_encabezado
     WHERE id = :id
     LIMIT 1"
);
$stmt->execute([':id' => $id_traspaso]);
$enc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enc) {
    echo json_encode(['ok' => false, 'mensaje' => 'Traspaso no encontrado.']);
    exit;
}

// Permisos básicos: mismo criterio que en la lista
$id_usuario = $_SESSION['user_id'] ?? 0;
$usuario_almacen = $pdo->query(
    "SELECT almacen, is_admin 
     FROM usuarios
     WHERE id_usuario = " . (int)$id_usuario
)->fetch(PDO::FETCH_ASSOC);

$id_almacen_usuario = $usuario_almacen['almacen'] ?? null;
$is_admin           = (int)($usuario_almacen['is_admin'] ?? 0);

if (!in_array($is_admin, [1, 2, 6], true) && $id_almacen_usuario !== null) {
    if ($enc['almacen_origen'] != $id_almacen_usuario &&
        $enc['almacen_destino'] != $id_almacen_usuario) {
        echo json_encode(['ok' => false, 'mensaje' => 'No tienes permiso para ver este traspaso.']);
        exit;
    }
}

// Detalle
$stmt = $pdo->prepare(
    "SELECT referencia, codigo, lote, caducidad, cantidad
     FROM traspasos_detalle
     WHERE id_traspaso = :id
     ORDER BY id"
);
$stmt->execute([':id' => $id_traspaso]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Armamos HTML del reporte
ob_start();
?>
<div>
    <h3>Traspaso entre almacenes</h3>
    <p>
        <strong>ID Traspaso:</strong> <?php echo (int)$enc['id']; ?><br>
        <strong>Fecha:</strong> <?php echo htmlspecialchars($enc['fecha']); ?><br>
        <strong>Folio:</strong> <?php echo htmlspecialchars($enc['folio']); ?><br>
        <strong>Almacén origen:</strong> <?php echo (int)$enc['almacen_origen']; ?><br>
        <strong>Almacén destino:</strong> <?php echo (int)$enc['almacen_destino']; ?><br>
    </p>

    <table border="1" cellpadding="4" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>Referencia</th>
            <th>Código</th>
            <th>Lote</th>
            <th>Caducidad</th>
            <th>Cantidad</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($detalles as $d): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['referencia']); ?></td>
                <td><?php echo htmlspecialchars($d['codigo']); ?></td>
                <td><?php echo htmlspecialchars($d['lote']); ?></td>
                <td><?php echo htmlspecialchars($d['caducidad']); ?></td>
                <td><?php echo (int)$d['cantidad']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$html = ob_get_clean();

echo json_encode([
    'ok'    => true,
    'folio' => $enc['folio'],
    'html'  => $html
]);
