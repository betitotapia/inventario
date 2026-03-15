<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php'; 
$datos=$pdo->query('SELECT * FROM productos ORDER BY ultima_modificacion DESC ')->fetchAll(PDO::FETCH_ASSOC); 
?>
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
                    <button class="btn btn-primary btn-sm" onclick="editarProducto(<?php echo $dato['id']; ?>)">Editar</button>
                    <button class="btn btn-danger btn-sm" onclick="eliminarProducto(<?php echo $dato['id']; ?>)">Eliminar</button>
                     
                    <?php } ?>

          </tbody>
        </table>
    <script>
         	var tabla = document.querySelector("#tabla");
             var dataTable = new DataTable(tabla);

    </script>