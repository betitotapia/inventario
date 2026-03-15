<!DOCTYPE html>
<html lang="en">
<head>
	<?php
	
	?>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>
<body>

<table id="tabla" class="table table-striped table-bordered">
          <thead>
                <tr>
                    <th>Código</th>
                    <th>Referencia</th>
                    <th>Lote</th>
                    <th>Caducidad</th>
                    <th>Cantidad</th>
                    <th>Almacen</th>
                    <th>Ubicacion</th>
                    <th>Acciones</th>
                </tr>
          </thead>
          <tbody>
        <?php foreach ($datos as $dato) { ?>
            <tr>
                <td><?php echo $dato['codigo']; ?></td>
                <td><?php echo $dato['referencia']; ?></td>
                <td><?php echo $dato['lote']; ?></td>
                <td><?php echo $dato['caducidad']; ?></td>
                <td><?php echo $dato['cantidad']; ?></td>
                <td><?php echo $dato['almacen'];
                ?></td>
                <td><?php echo $dato['ubicacion']; ?></td>
                <td>
                   
                    <?php } ?>

          </tbody>
        </table>