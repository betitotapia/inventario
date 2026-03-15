<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php'; 
$datos=$pdo->query('SELECT * FROM almacenes ORDER BY id_almacen ASC ')->fetchAll(PDO::FETCH_ASSOC); 
?>
<table id="tabla" class="table table-striped table-bordered">
          <thead>
                <tr>
                    <th>NO. ALMACEN</th>
                    <th>NOMBRE</th>
                </tr>
          </thead>
          <tbody>
        <?php foreach ($datos as $dato) { ?>
            <tr>
                <td><?php echo $dato['id_almacen']; ?></td>
                <td><a href="detalle_almacen.php?id_almacen=<?php echo $dato['id_almacen'] ?>"><?php echo $dato['nombre_almacen']; ?></td></a>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="editarProducto(<?php echo $dato['id_almacen']; ?>)">Editar</button>
                    <button class="btn btn-danger btn-sm" onclick="eliminarProducto(<?php echo $dato['id_almacen']; ?>)">Eliminar</button>
                     
                    <?php } ?>

          </tbody>
        </table>
    <script>
         	var tabla = document.querySelector("#tabla");
             var dataTable = new DataTable(tabla);

    </script>