<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_usuario = $_SESSION['user_id'] ?? null;

if (!isset($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
    die('Error al subir el archivo.');
}

$nombreTmp = $_FILES['archivo_excel']['tmp_name'];

$handle = fopen($nombreTmp, 'r');
if (!$handle) {
    die('No se pudo abrir el archivo.');
}

/**
 * Detecta delimitador (coma o punto y coma) usando la primera línea.
 */
$primeraLinea = fgets($handle);
if ($primeraLinea === false) {
    fclose($handle);
    die('El archivo está vacío.');
}
$delim = (substr_count($primeraLinea, ';') > substr_count($primeraLinea, ',')) ? ';' : ',';
rewind($handle);

// Leer encabezados (1ª línea) y descartarlos
$encabezados = fgetcsv($handle, 0, $delim);

try {
    $pdo->beginTransaction();

    $insertadas = 0;
    $saltadas = 0;
    $errores = [];

    $numLinea = 1; // ya leímos encabezados
    while (($fila = fgetcsv($handle, 0, $delim)) !== false) {
        $numLinea++;

        // Si la fila viene vacía (por saltos de línea al final)
        if ($fila === [null] || count(array_filter($fila, fn($x) => trim((string)$x) !== '')) === 0) {
            continue;
        }

        if (count($fila) < 5) {
            $saltadas++;
            $errores[] = "L{$numLinea}: Fila inválida (menos de 5 columnas). Leído: " . implode(' | ', $fila);
            continue;
        }

        $referencia     = trim((string)$fila[0]);
        $lote           = trim((string)$fila[1]);
        $cantidad       = (int)trim((string)$fila[2]);
        $almacenOrigen  = (int)trim((string)$fila[3]);
        $almacenDestino = (int)trim((string)$fila[4]);

        if ($referencia === '' || $lote === '' || $cantidad <= 0 || $almacenOrigen <= 0 || $almacenDestino <= 0) {
            $saltadas++;
            $errores[] = "L{$numLinea}: Datos inválidos ref='{$referencia}', lote='{$lote}', cant='{$cantidad}', ao='{$almacenOrigen}', ad='{$almacenDestino}'.";
            continue;
        }

        if ($almacenOrigen === $almacenDestino) {
            $saltadas++;
            $errores[] = "L{$numLinea}: Almacén origen y destino son iguales ({$almacenOrigen}).";
            continue;
        }

        // Buscar producto origen por referencia, lote y almacén con stock suficiente
        $stmt = $pdo->prepare(
            "SELECT * FROM productos
             WHERE referencia = :ref
               AND lote = :lote
               AND almacen = :almacen
               AND cantidad >= :cant
             LIMIT 1"
        );
        $stmt->execute([
            ':ref'     => $referencia,
            ':lote'    => $lote,
            ':almacen' => $almacenOrigen,
            ':cant'    => $cantidad
        ]);
        $prodOrigen = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prodOrigen) {
            $saltadas++;
            $errores[] = "L{$numLinea}: No encontrado o sin stock ref='{$referencia}', lote='{$lote}', ao='{$almacenOrigen}', cant_req='{$cantidad}'.";
            continue;
        }

        // Crear encabezado por línea (si quieres agrupar todo en 1 traspaso, te lo ajusto)
        $folioCsv = 'CSV-' . date('YmdHis') . '-' . $numLinea;

        $stmtEnc = $pdo->prepare(
            "INSERT INTO traspasos_encabezado
             (fecha, folio, almacen_origen, almacen_destino, id_usuario)
             VALUES (CURDATE(), :folio, :ao, :ad, :user)"
        );
        $stmtEnc->execute([
            ':folio' => $folioCsv,
            ':ao'    => $almacenOrigen,
            ':ad'    => $almacenDestino,
            ':user'  => $id_usuario
        ]);
        $idTraspaso = (int)$pdo->lastInsertId();

        // Restar del origen
        $stmt = $pdo->prepare(
            "UPDATE productos
             SET cantidad = cantidad - :cant,
                 ultima_modificacion = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':cant' => $cantidad,
            ':id'   => $prodOrigen['id']
        ]);

        // Buscar en destino (misma referencia + lote)
        $stmt = $pdo->prepare(
            "SELECT id FROM productos
             WHERE referencia = :ref
               AND lote = :lote
               AND almacen = :almacen_dest
             LIMIT 1"
        );
        $stmt->execute([
            ':ref'          => $referencia,
            ':lote'         => $lote,
            ':almacen_dest' => $almacenDestino
        ]);
        $prodDestino = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($prodDestino) {
            // Sumar en destino
            $stmt = $pdo->prepare(
                "UPDATE productos
                 SET cantidad = cantidad + :cant,
                     ultima_modificacion = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([
                ':cant' => $cantidad,
                ':id'   => $prodDestino['id']
            ]);
            $idProductoDestino = (int)$prodDestino['id'];
        } else {
            // Crear producto en destino
            $stmt = $pdo->prepare(
                "INSERT INTO productos
                 (codigo, lote, caducidad, referencia, cantidad, almacen, ubicacion, ultima_modificacion)
                 VALUES (:codigo, :lote, :caducidad, :referencia, :cantidad, :almacen, :ubicacion, NOW())"
            );
            $stmt->execute([
                ':codigo'     => $prodOrigen['codigo'] ?? '',
                ':lote'       => $prodOrigen['lote'],
                ':caducidad'  => $prodOrigen['caducidad'] ?? null,
                ':referencia' => $prodOrigen['referencia'],
                ':cantidad'   => $cantidad,
                ':almacen'    => $almacenDestino,
                ':ubicacion'  => $prodOrigen['ubicacion'] ?? ''
            ]);
            $idProductoDestino = (int)$pdo->lastInsertId();
        }

        // Guardar detalle
        $stmtDet = $pdo->prepare(
            "INSERT INTO traspasos_detalle
             (id_traspaso, id_producto_origen, id_producto_destino,
              referencia, codigo, lote, caducidad, cantidad)
             VALUES
             (:id_tras, :id_po, :id_pd, :ref, :cod, :lote, :cad, :cant)"
        );
        $stmtDet->execute([
            ':id_tras' => $idTraspaso,
            ':id_po'   => $prodOrigen['id'],
            ':id_pd'   => $idProductoDestino,
            ':ref'     => $prodOrigen['referencia'],
            ':cod'     => $prodOrigen['codigo'] ?? '',
            ':lote'    => $prodOrigen['lote'],
            ':cad'     => $prodOrigen['caducidad'] ?? null,
            ':cant'    => $cantidad
        ]);

        $insertadas++;
    }

    $pdo->commit();
    fclose($handle);

    $mensaje = "Importación finalizada.\nInsertadas: {$insertadas}\nSaltadas: {$saltadas}";
    if (!empty($errores)) {
        $mensaje .= "\n\nDetalles (primeros 15):\n" . implode("\n", array_slice($errores, 0, 15));
    }

    echo "<script>alert(" . json_encode($mensaje) . ");window.location='traspaso_almacen.php';</script>";
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fclose($handle);
    echo "Error al procesar el archivo: " . htmlspecialchars($e->getMessage());
}