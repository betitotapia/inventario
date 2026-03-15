<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
$user_id = $_SESSION['user_id'];

$usuario_almacen = $pdo->query("SELECT almacen, is_admin FROM usuarios WHERE id_usuario = ".$user_id."")->fetch(PDO::FETCH_ASSOC);
$id_almacen_usuario=$usuario_almacen['almacen'];
$is_admin=$usuario_almacen['is_admin'];


    $datos=$pdo->query('SELECT m.id_movimiento, m.id_producto, m.cantidad, m.tipo_movimiento, m.id_usuario,m.date_created, 
p.id, p.codigo, p.referencia, p.lote, p.caducidad, p.cantidad AS existencia, p.almacen, p.ubicacion, p.ultima_modificacion
FROM movimientos m INNER JOIN productos p ON m.id_producto= p.id  WHERE m.id_usuario=25 AND p.almacen=28')->fetchAll(PDO::FETCH_ASSOC); 

echo "
<table id='tabla' class='table table-striped table-bordered' >
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
          <tbody>";
          
         foreach ($datos as $dato) { 
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
                default:
                    $tipo_mov = 'Sin movimiento';
                    $color = 'black';
                    break;
            }
            
            echo "
            <tr>
                <td>{$dato['codigo']}</td>
                <td>{$dato['referencia']}</td>
                <td>{$dato['lote']}</td>
                <td>{$dato['caducidad']}</td>
                <td style='background-color: {$color}; color:white;'>{$tipo_mov}</td>
                <td>{$dato['cantidad']}</td>
                <td>{$dato['almacen']}</td>
                <td>{$dato['ubicacion']}</td>
                <td>{$dato['date_created']}</td>
                <td>";
                $datos_usuario = $pdo->query("SELECT nombre FROM usuarios WHERE id_usuario = " . $dato['id_usuario'])->fetch(PDO::FETCH_ASSOC);
                echo $datos_usuario['nombre'];
            echo "</td>
               
            </tr>";
         }
         
        echo "
          </tbody>
             </table>
        <script>
                var tabla = document.querySelector('#tabla');
                var dataTable = new DataTable(tabla);
        </script>";

?>
