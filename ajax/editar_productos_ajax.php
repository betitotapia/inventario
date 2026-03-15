<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

if (isset($_POST['id'])){$id=intval($_POST['id']);}
if (isset($_POST['lote'])){$lote=$_POST['lote'];}
if (isset($_POST['caducidad'])){$caducidad=$_POST['caducidad'];}
if (isset($_POST['referencia'])){$referencia=$_POST['referencia'];}

$id_usuario=$_SESSION['user_id'];

if (isset($_GET['id'])){

	if(isset($_GET['tipo'])){

		$tipo=$_GET['tipo'];


	switch ($tipo){

		case "referencia":

		$referencia=$_GET['item'];
		$id=($_GET['id']);
		// Preparar la consulta con parámetros
			$sql = "UPDATE productos SET referencia = :referencia WHERE id = :id";
			$stmt = $pdo->prepare($sql);

		// Ejecutar la consulta con valores seguros
			$stmt->execute([
				':referencia' => $referencia,
				':id' => $id
			]);
				$sql_mov = "INSERT INTO movimientos (
		id_producto, 
			cantidad, 
			tipo_movimiento, 
			id_usuario, 
			date_created, 
			campo_actualizado, 
			cantidad_anterior
		) VALUES (
			:id_producto, 
			:cantidad, 
			:tipo_movimiento, 
			:id_usuario, 
			NOW(), 
			:campo_actualizado, 
			:cantidad_anterior
		)";
		$stmt_2 = $pdo->prepare($sql_mov);

			$stmt_2->execute([
				':id_producto'       => $id,
				':cantidad'          => 0,
				':tipo_movimiento'   => 8,
				':id_usuario'        => $id_usuario,
				':campo_actualizado' => 'referencia',
				':cantidad_anterior' => 0
			]);

	    break;
		
		case "lote":

		$lote=$_GET['item'];
		$id=($_GET['id']);
		// Preparar la consulta con parámetros
			$sql = "UPDATE productos SET lote = :lote WHERE id = :id";
			$stmt = $pdo->prepare($sql);

		// Ejecutar la consulta con valores seguros
			$stmt->execute([
				':lote' => $lote,
				':id' => $id
			]);

			$sql_mov = "INSERT INTO movimientos (
		id_producto, 
			cantidad, 
			tipo_movimiento, 
			id_usuario, 
			date_created, 
			campo_actualizado, 
			cantidad_anterior
		) VALUES (
			:id_producto, 
			:cantidad, 
			:tipo_movimiento, 
			:id_usuario, 
			NOW(), 
			:campo_actualizado, 
			:cantidad_anterior
		)";
		$stmt_2 = $pdo->prepare($sql_mov);

			$stmt_2->execute([
				':id_producto'       => $id,
				':cantidad'          => 0,
				':tipo_movimiento'   => 8,
				':id_usuario'        => $id_usuario,
				':campo_actualizado' => 'lote',
				':cantidad_anterior' => 0
			]);
		
		break;

	   case "caducidad":

		$caducidad=$_GET['item'];
		$id=($_GET['id']);
		echo"<scipt>console.log('caducidad ".$caducidad."')</script>";
		// Preparar la consulta con parámetros
			$sql = "UPDATE productos SET caducidad = :caducidad WHERE id = :id";
			$stmt = $pdo->prepare($sql);

		// Ejecutar la consulta con valores seguros
			$stmt->execute([
				':caducidad' => $caducidad,
				':id' => $id
			]);
		break;

		case "cantidad":

		$cantidad=$_GET['item'];
		$id=($_GET['id']);
		$c_cantidad='cantidad';
		$cantidad_vieja=$_GET['cantidad_vieja'];
		// Preparar la consulta con parámetros
			$sql = "UPDATE productos SET cantidad = :cantidad WHERE id = :id";
			$stmt = $pdo->prepare($sql);
			
			// Ejecutar la consulta con valores seguros
			$stmt->execute([
				':cantidad' => $cantidad,
				':id' => $id
			]);

		$sql_mov = "INSERT INTO movimientos (
		id_producto, 
			cantidad, 
			tipo_movimiento, 
			id_usuario, 
			date_created, 
			campo_actualizado, 
			cantidad_anterior
		) VALUES (
			:id_producto, 
			:cantidad, 
			:tipo_movimiento, 
			:id_usuario, 
			NOW(), 
			:campo_actualizado, 
			:cantidad_anterior
		)";
		$stmt_2 = $pdo->prepare($sql_mov);

			$stmt_2->execute([
				':id_producto'       => $id,
				':cantidad'          => $cantidad,
				':tipo_movimiento'   => 8,
				':id_usuario'        => $id_usuario,
				':campo_actualizado' => $c_cantidad,
				':cantidad_anterior' => $cantidad_vieja
			]);


		break;

	  }
	
	}
}
		

?>


?>