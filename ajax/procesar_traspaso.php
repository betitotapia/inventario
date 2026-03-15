<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

header('Content-Type: application/json; charset=utf-8');

$id_usuario     = $_SESSION['user_id'] ?? null; // >>>> guardamos usuario que hace el traspaso

$fecha          = $_POST['fecha']          ?? '';
$almacenOrigen  = (int)($_POST['almacen_origen']  ?? 0);
$almacenDestino = (int)($_POST['almacen_destino'] ?? 0);
$folio          = trim($_POST['folio'] ?? '');
$detalleJson    = $_POST['detalle'] ?? '';

if (!$fecha || !$almacenOrigen || !$almacenDestino || !$folio || !$detalleJson) {
    echo json_encode(['ok' => false, 'mensaje' => 'Datos incompletos.']);
    exit;
}

$detalle = json_decode($detalleJson, true);
if (!is_array($detalle) || count($detalle) === 0) {
    echo json_encode(['ok' => false, 'mensaje' => 'Detalle vacío.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // >>>> 1) Insertamos el ENCABEZADO del traspaso
    $stmtEnc = $pdo->prepare(
        "INSERT INTO traspasos_encabezado
         (fecha, folio, almacen_origen, almacen_destino, id_usuario)
         VALUES (:fecha, :folio, :ao, :ad, :user)"
    );
    $stmtEnc->execute([
        ':fecha' => $fecha,
        ':folio' => $folio,
        ':ao'    => $almacenOrigen,
        ':ad'    => $almacenDestino,
        ':user'  => $id_usuario
    ]);
    $idTraspaso = (int)$pdo->lastInsertId();   // >>>> id del encabezado

    $lineasReporte = [];

    foreach ($detalle as $item) {
        $idProductoOrigen = (int)$item['id_producto_origen'];
        $cantTraspaso     = (int)$item['cantidad'];
        $ref              = $item['referencia'];
        $cod              = $item['codigo'];
        $lote             = $item['lote'];
        $cad              = $item['caducidad'];

        // Verificar existencia en almacén origen
        $stmt = $pdo->prepare(
            "SELECT * FROM productos 
             WHERE id = :id AND almacen = :almacen AND cantidad >= :cant
             LIMIT 1"
        );
        $stmt->execute([
            ':id'      => $idProductoOrigen,
            ':almacen' => $almacenOrigen,
            ':cant'    => $cantTraspaso
        ]);
        $prodOrigen = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prodOrigen) {
            throw new Exception("Sin existencia suficiente para referencia {$ref}, lote {$lote}.");
        }

        // Restar del almacén origen
        $stmt = $pdo->prepare(
            "UPDATE productos
             SET cantidad = cantidad - :cant,
                 ultima_modificacion = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':cant' => $cantTraspaso,
            ':id'   => $idProductoOrigen
        ]);

        // Buscar producto en almacén destino con misma referencia + lote
        $stmt = $pdo->prepare(
            "SELECT id, cantidad FROM productos
             WHERE referencia = :ref
               AND lote = :lote
               AND almacen = :almacen_dest
             LIMIT 1"
        );
        $stmt->execute([
            ':ref'          => $ref,
            ':lote'         => $lote,
            ':almacen_dest' => $almacenDestino
        ]);
        $prodDestino = $stmt->fetch(PDO::FETCH_ASSOC);

        $idProductoDestino = null; // >>>>

        if ($prodDestino) {
            // Sumar cantidad
            $stmt = $pdo->prepare(
                "UPDATE productos
                 SET cantidad = cantidad + :cant,
                     ultima_modificacion = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([
                ':cant' => $cantTraspaso,
                ':id'   => $prodDestino['id']
            ]);
            $idProductoDestino = (int)$prodDestino['id']; // >>>>
        } else {
            // Copiar registro con el nuevo almacén y cantidad
            $stmt = $pdo->prepare(
                "INSERT INTO productos
                 (codigo, lote, caducidad, referencia, cantidad, almacen, ubicacion, ultima_modificacion)
                 VALUES (:codigo, :lote, :caducidad, :referencia, :cantidad, :almacen, :ubicacion, NOW())"
            );
            $stmt->execute([
                ':codigo'     => $prodOrigen['codigo'],
                ':lote'       => $prodOrigen['lote'],
                ':caducidad'  => $prodOrigen['caducidad'],
                ':referencia' => $prodOrigen['referencia'],
                ':cantidad'   => $cantTraspaso,
                ':almacen'    => $almacenDestino,
                ':ubicacion'  => $prodOrigen['ubicacion']
            ]);
            $idProductoDestino = (int)$pdo->lastInsertId(); // >>>>
        }

        // >>>> 2) Insertamos el DETALLE del traspaso
        $stmtDet = $pdo->prepare(
            "INSERT INTO traspasos_detalle
             (id_traspaso, id_producto_origen, id_producto_destino,
              referencia, codigo, lote, caducidad, cantidad)
             VALUES
             (:id_tras, :id_po, :id_pd, :ref, :cod, :lote, :cad, :cant)"
        );
        $stmtDet->execute([
            ':id_tras' => $idTraspaso,
            ':id_po'   => $idProductoOrigen,
            ':id_pd'   => $idProductoDestino,
            ':ref'     => $ref,
            ':cod'     => $cod,
            ':lote'    => $lote,
            ':cad'     => $cad ?: null,
            ':cant'    => $cantTraspaso
        ]);

        $lineasReporte[] = [
            'referencia' => $ref,
            'codigo'     => $cod,
            'lote'       => $lote,
            'caducidad'  => $cad,
            'cant'       => $cantTraspaso
        ];
    }

    $pdo->commit();

    // Reporte HTML
    ob_start();
    ?>
    <h3>Traspaso entre almacenes</h3>
    <p>
        Fecha: <?php echo htmlspecialchars($fecha); ?><br>
        Folio: <?php echo htmlspecialchars($folio); ?><br>
        ID Traspaso: <?php echo (int)$idTraspaso; ?><br>
        Origen: <?php echo (int)$almacenOrigen; ?> |
        Destino: <?php echo (int)$almacenDestino; ?>
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
        <?php foreach ($lineasReporte as $l): ?>
            <tr>
                <td><?php echo htmlspecialchars($l['referencia']); ?></td>
                <td><?php echo htmlspecialchars($l['codigo']); ?></td>
                <td><?php echo htmlspecialchars($l['lote']); ?></td>
                <td><?php echo htmlspecialchars($l['caducidad']); ?></td>
                <td><?php echo (int)$l['cant']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    $htmlReporte = ob_get_clean();

    echo json_encode([
        'ok'            => true,
        'folio'         => $folio,
        'id_traspaso'   => $idTraspaso, // >>>> por si lo quieres usar en otras pantallas
        'html_reporte'  => $htmlReporte
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'ok'      => false,
        'mensaje' => $e->getMessage()
    ]);
}
