<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
    require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
		// escaping, additionally removing everything that could be (html/javascript-) code
		$id_producto = $_POST["mod_id"];
        $ubicacion = $_POST["mod_ubicacion"];
        echo "<script>console.log('producto".$_POST['mod_id']."');</script>";
	    echo "<script>console.log('work_facturas:".$ubicacion." producto".$id_producto."');</script>";
		$sql="UPDATE productos SET ubicacion='".$ubicacion."' WHERE id ='".$id_producto."';";
		$pdo->prepare("UPDATE productos SET ubicacion = ? WHERE id = ?")
		->execute([$ubicacion,$id_producto]);
		echo "La ubicación ha sido actualizada satisfactoriamente.";

		
		

?>