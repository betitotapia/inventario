<table id='tabla2' class='table table-striped table-bordered' >
          <thead>
                <tr>
                    <th>Código</th>
                    <th>Referencia</th>
                    <th>Lote</th>
                    <th>Caducidad</th>
                    <th>Tipo Movimiento</th>
                    <th>Cantidad</th>
                    <th>Almacen</th>
                    <th>Ubicacion</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                </tr>
          </thead>
          <tbody>

        <?php  foreach ($datos as $dato) {
            switch ($dato['tipo_movimiento']) {
                case '1':
                    $tipo_mov = 'Entrada';
                    $color = 'green';
                    break;
                case '2':
                    $tipo_mov = 'Salida';
                    $color = 'red';
                    break;
                case '3':
                    $tipo_mov = 'Ajuste de entrada';
                    $color = 'blue';
                    break; 
                case '4':
                    $tipo_mov = 'Ajuste de salida';
                    $color = '#d800d4';
                    break;
                case '5':
                    $tipo_mov = 'Entrada por vale de prestamo';
                    $color = '#d89a00';
                    break;  
                case '6':
                    $tipo_mov = 'Salida por vale de prestamo';  
                    $color = '#00d8d1';
                    break;

            } ?>
        
            <tr>
                <td><?php echo $dato['codigo'] ?> </td>
                <td><?php echo $dato['referencia'] ?> </td>
                <td><?php echo $dato['lote'] ?> </td>
                <td><?php echo $dato['caducidad'] ?> </td>
                <td style='background-color: <? echo $color; ?> color:white;'> <?php echo $tipo_mov ?></td>
                <td><?php echo $dato['cantidad'] ?> </td>
                <td><?php echo $dato['almacen'] ?> </td>
                <td><?php echo $dato['ubicacion'] ?> </td>
                <td><?php echo $dato['date_created'] ?> </td>
                <td>
              <?php   $datos_usuario = $pdo->query("SELECT nombre FROM usuarios WHERE id_usuario = " . $dato['id_usuario'])->fetch(PDO::FETCH_ASSOC);
                echo $datos_usuario['nombre'];?>
            </td>
                
            </tr>
        <?php  } ?>
         
          </tbody>
             </table>
        