<?php
include 'db.php';

$codigo = $_POST['codigo'];
$lote = $_POST['lote'];
$caducidad = $_POST['caducidad'];
$referencia = isset($_POST['referencia']) ? $_POST['referencia'] : '';
$operacion = $_POST['operacion'] ?? 'sumar';
$final_codigo=$_POST['final_codigo'];
 

$stmt = $pdo->prepare("SELECT * FROM productos WHERE codigo = ? AND lote = ?");
$stmt->execute([$codigo, $lote]);
$producto = $stmt->fetch();

if ($producto) {

    if ($operacion === "sumar") {
        $update = $pdo->prepare("UPDATE productos SET cantidad = cantidad + 1 WHERE id = ?");
        $update->execute([$producto['id']]);
        echo "Producto actualizado (+1).";
    } else {
        if ($producto['cantidad'] > 0) {
            $update = $pdo->prepare("UPDATE productos SET cantidad = cantidad - 1 WHERE id = ?");
            $update->execute([$producto['id']]);
            echo "Producto actualizado (-1).";
        } else {
            echo "Sin existencias para restar.";
        }
    }
} else {

        $stmt_ref = $pdo->prepare("SELECT * FROM productos WHERE codigo = ? ");
        $stmt_ref->execute([$codigo]);
        $producto = $stmt_ref->fetch();
        if ($producto) {
            $referencia = $producto['referencia'];
            print_r($referencia);
            $insert = $pdo->prepare("INSERT INTO productos (codigo, lote, caducidad, referencia, cantidad) VALUES (?, ?, ?, ?, 1)");
            $insert->execute([$codigo, $lote, $caducidad, $referencia]);
            echo "Producto agregado correctamente.";
        }

    if ($operacion === "restar") {
        echo "Producto no encontrado para restar.";
    } elseif ($referencia !== '') {
       $insert = $pdo->prepare("INSERT INTO productos (codigo, lote, caducidad, referencia, cantidad) VALUES (?, ?, ?, ?, 1)");
        $insert->execute([$codigo, $lote, $caducidad, $referencia]);
        echo "Producto agregado correctamente.";
    } else {
        echo "NECESITA_REFERENCIA";
    }
}
