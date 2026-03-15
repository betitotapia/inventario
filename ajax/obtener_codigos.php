 <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php'; 
$datos=$pdo->query('SELECT * FROM etiquetas ORDER BY fecha_creacion DESC ')->fetchAll(PDO::FETCH_ASSOC); 
?>
<table id="tabla" class="table table-striped table-bordered">
   
   <thead>
                        <tr>
                            <th>ID</th>
                            <th>Referencia</th>
                            <th>Lote</th>
                            <th>Caducidad</th>
                            <th>Código Barras</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
   <?php foreach ($datos as $dato) { ?>
    <tr>
        <td><?php echo $dato['id']; ?></td>
        <td><?php echo $dato['referencia']; ?></td>
        <td><?php echo $dato['lote']; ?></td>
        <td><?php echo $dato['caducidad']; ?></td>
        <td><?php echo $dato['codigo_barras']; ?></td>
        <td><button class="reimprimir-btn" onclick=" reimprimirEtiqueta(<?php echo $dato['id'];?>)"  data-id="">Reimprimir</button></td>
   
   <?php  } ?>

                    </tbody>
                </table>
                <script>
         	var tabla = document.querySelector("#tabla");
             var dataTable = new DataTable(tabla);

    </script>
  