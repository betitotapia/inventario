<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

//Archivo de funciones PHP
//include("../../funciones.php");

 

  $datos=$pdo->query('SELECT m.id_movimiento, m.id_producto, m.cantidad, m.tipo_movimiento, m.id_usuario,m.date_created, 
p.id, p.codigo, p.referencia, p.lote, p.caducidad, p.cantidad AS existencia, p.almacen, p.ubicacion, p.ultima_modificacion
FROM movimientos m INNER JOIN productos p ON m.id_producto= p.id  WHERE m.id_usuario=25 AND p.almacen=28')->fetchAll(PDO::FETCH_ASSOC); 

       $file="reporte_inventario_de_almacen_26.xls";

       header("Content-Type: application/vnd.ms-excel; charset=iso-8859-1");
       header("Content-Disposition: attachment; filename=$file");
       include(dirname('__FILE__').'/rep_html/reporte_almacen_html_26.php');

			?>