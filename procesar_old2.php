<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

$codigo = $_POST['codigo'];
$lote = $_POST['lote'];
$caducidad = $_POST['caducidad'];
$referencia = $_POST['referencia'] ?? '';
$operacion = $_POST['operacion'] ?? 'sumar';
$cantidad_ajuste = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
$id_usuario=$_SESSION['user_id'];
$datos = $pdo->query("SELECT * FROM usuarios WHERE id_usuario = ".$id_usuario."")->fetch(PDO::FETCH_ASSOC);
$almacen = $datos['almacen'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM productos WHERE codigo = ? AND lote = ? AND almacen = ?");;
$stmt->execute([$codigo, $lote, $almacen]);
$producto = $stmt->fetch();

if ($producto) {
    $id_producto = $producto['id'];
    switch ($operacion) {
        case "sumar":
            $pdo->prepare("UPDATE productos SET cantidad = cantidad + 1, ultima_modificacion = NOW() WHERE id = ?")->execute([$producto['id']]);
            $pdo->prepare("INSERT INTO movimientos (id_producto, cantidad, tipo_movimiento, id_usuario, date_created) VALUES (?, ?, ?, ?, NOW())")
            ->execute([$id_producto, 1,1,$id_usuario ]);
            echo "Producto actualizado (+1).";
            break;
            
        case "restar":
            if ($producto['cantidad'] > 0) {
                $pdo->prepare("UPDATE productos SET cantidad = cantidad - 1, ultima_modificacion = NOW() WHERE id = ?")->execute([$producto['id']]);
                $pdo->prepare("INSERT INTO movimientos (id_producto, cantidad, tipo_movimiento, id_usuario, date_created) VALUES (?, ?, ?, ?, NOW())")
            ->execute([$id_producto, 1,2,$id_usuario]);
                echo "Producto actualizado (-1).";
            } else {
                echo "Sin existencias para restar.";
            }
            break;
            
        case "ajuste":
            $nueva_cantidad = $producto['cantidad'] + $cantidad_ajuste;
            if ($nueva_cantidad >= 0) {
                if($nueva_cantidad > $producto['cantidad']){
                   $tipo_movimiento=3;
                }
                else{
                    $tipo_movimiento=4;
                }
                $pdo->prepare("UPDATE productos SET cantidad = ?, ultima_modificacion = NOW() WHERE id = ?")->execute([$nueva_cantidad, $producto['id']]);
                $pdo->prepare("INSERT INTO movimientos (id_producto, cantidad, tipo_movimiento, id_usuario, date_created) VALUES (?, ?, ?, ?, NOW())")
                ->execute([$id_producto, $cantidad_ajuste,$tipo_movimiento,$id_usuario]);
                echo "Ajuste realizado. Nueva cantidad: " . $nueva_cantidad;
            } else {
                echo "Error: La cantidad no puede ser negativa.";
            }
            break;
    }
} else {
    // Verificar si el código existe (aunque sea con otro lote)
    $stmtCod = $pdo->prepare("SELECT referencia FROM productos WHERE codigo = ? LIMIT 1");
    $stmtCod->execute([$codigo]);
    $base = $stmtCod->fetch();
    $id_usuario=$_SESSION['user_id'];

    if ($operacion === "restar") {
        echo "Producto no encontrado para restar.";
    } elseif ($base) {
        
        // Usar referencia existente 
        $pdo->prepare("INSERT INTO productos (codigo, lote, caducidad, referencia, cantidad, almacen,ultima_modificacion) VALUES (?, ?, ?, ?, ?,?,NOW())")
            ->execute([$codigo, $lote, $caducidad, $base['referencia'], ($operacion === 'ajuste') ? $cantidad_ajuste : 1, $almacen]);
            $id_insertado = $pdo->lastInsertId(); 

        $pdo->prepare("INSERT INTO movimientos (id_producto, cantidad, tipo_movimiento, id_usuario, date_created) VALUES (?, ?, ?, ?, NOW())")
        ->execute([$id_insertado, 1,1,$id_usuario ]);
        echo "Nuevo lote agregado con referencia existente.";

    } elseif (!empty($referencia)) {

        // Usar referencia proporcionada
        $pdo->prepare("INSERT INTO productos (codigo, lote, caducidad, referencia, cantidad,almacen,ultima_modificacion) VALUES (?, ?, ?, ?, ?, ?,NOW())")
        ->execute([$codigo, $lote, $caducidad, $referencia, ($operacion === 'ajuste') ? $cantidad_ajuste : 1, $almacen]);
        $id_insertado = $pdo->lastInsertId(); 

        $pdo->prepare("INSERT INTO movimientos (id_producto, cantidad, tipo_movimiento, id_usuario, date_created) VALUES (?, ?, ?, ?, NOW())")
        ->execute([$id_insertado, 1,1,$id_usuario ]);
        echo "Producto insertado.";

    } else {
        echo "NECESITA_REFERENCIA";
    }
}