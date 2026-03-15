<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

//Archivo de funciones PHP
//include("../../funciones.php");

  $id_almacen=$_GET['id_almacen'];

  $datos=$pdo->query('SELECT * FROM productos where almacen='.$id_almacen.' ')->fetchAll(PDO::FETCH_ASSOC); 

       $file="reporte_inventario_de_almacen_".$id_almacen.".xls";
       header("Content-Type: application/vnd.ms-excel; charset=iso-8859-1");
       header("Content-Disposition: attachment; filename=$file");
       include(dirname('__FILE__').'/rep_html/reporte_almacenes_html.php');

			?>