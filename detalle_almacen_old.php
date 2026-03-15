<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
$id_usuario = $_SESSION['user_id'];
$usuario_almacen=$pdo->query("SELECT almacen, is_admin FROM usuarios WHERE id_usuario = ".$id_usuario."")->fetch(PDO::FETCH_ASSOC);
$id_almacen_usuario=$usuario_almacen['almacen'];
$is_admin=$usuario_almacen['is_admin'];

    $id_almacen = $_GET['id_almacen'];
    if ($id_almacen==$id_almacen_usuario || $is_admin === 1 || $is_admin === 6 || $is_admin === 2 ) {
    $datos = $pdo->query("SELECT * FROM productos WHERE almacen = '".$id_almacen."' and cantidad > 0")->fetchAll(PDO::FETCH_ASSOC);
   echo "<script>console.log('".$id_almacen."')</script>";
    }else{
        echo "<script>alert('No tienes permiso para ver este almacen'); window.location='almacenes.php';</script>";
    }

?>
<!DOCTYPE html>
<html lang="es">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/head.php'; ?>

<body>
    <div style="margin-bottom:5%;">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/navbar.php'; ?>
    </div>
    <div style="margin-left: 1%;">
        <h2>ALMACEN <?php echo $id_almacen; ?></h2>
    </div>
    <div style="display: flex; margin-left:50%;padding:5px 5px;" >
    <label style="background-color: red; text-align:center;">CADUCOS</label>
        <label style="background-color: orange; text-align:center;">6 MESES PARA CADUCAR</label>
        <label style="background-color: yellow; text-align:center;">MAS DE 6 MESES PARA CADUCAR</label>
        <label style="background-color: green; text-align:center;">MAS DE 1 AÑO PARA CADUCAR</label>
        
    </div>
    <div class="col-md-2" style="margin-left: 2%;">
        <button type="button" class="btn btn-sm btn-success" onclick='descargar_excel(<?php echo $id_almacen; ?>);'>
            <span class="glyphicon glyphicon-download"></span> Descargar Excel</button>

    </div>
    <div class="container-fluid">
        <div class="card-body p-0 outer_div" id="tabla-completa">
            <table id="tabla" class="table table-striped table-bordered">
                <thead>
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
                    <?php foreach ($datos as $dato) {
                        $fechaCaducidad = new DateTime($dato['caducidad']);           
                        $hoy = new DateTime();
                        $diferencia = $hoy->diff($fechaCaducidad);
                        // Calculamos la diferencia en meses
                        $mesesDiferencia = ($diferencia->y * 12) + $diferencia->m;
                        if ($fechaCaducidad <= $hoy) {
                            // Si ya caducó
                            $bgColor = 'red';
                        } elseif ($mesesDiferencia <= 6) {
                            // Menos de 6 meses para caducar
                            $bgColor = 'orange';
                        } elseif ($mesesDiferencia <= 12) {
                            // Entre 6 meses y 1 año para caducar
                            $bgColor = 'yellow';
                        } else {
                            // Más de 1 año para caducar
                            $bgColor = 'green';
                        }

            ?>
                    <tr>
                        <td><?php echo $dato['codigo']; ?></td>
                        <td data-search="<?php echo $dato['referencia']; ?>">
                        
                        <?php 
                        if ($is_admin == 1 ) {

                            echo "<span style='display:none;'>".$dato['referencia']."</span>";   
                            echo '<input type="text" class="form-control" id="referencia_'.$dato['id'].'" name="referencia" value="'.$dato['referencia'].'" readonly ondblclick="edit_item('.$dato['id'].', \'referencia_'.$dato['id'].'\')" onfocusout="save_item('.$dato['id'].',0)">';

                        }else{
                                 echo $dato['referencia']; 
                        }
                       ?></td>
                        <td data-search="<?php echo $dato['lote']; ?>">
                            
                            <?php 
                            
                              if ($is_admin == 1 ) {
                        echo "<span style='display:none;'>".$dato['lote']."</span>"; 
                           echo '<input type="text" class="form-control" id="lote_'.$dato['id'].'" name="lote" value="'.$dato['lote'].'" readonly ondblclick="edit_item('.$dato['id'].', \'lote_'.$dato['id'].'\')" onfocusout="save_item('.$dato['id'].',1)">';
                        }else{
                                 echo $dato['lote']; 
                                 
                        }
                        ?></td>
                          
                        <td style="background-color:<?php echo $bgColor;?>"> <?php echo $dato['caducidad']; ?></td>
                        <td>
                        <?php 
                            
                              if ($is_admin == 1 ) {
                        echo "<span style='display:none;'>".$dato['cantidad']."</span>";
                       echo "<input type='number'class='form-control' id='cantidad_".$dato['id']."' name='cantidad' value='".$dato['cantidad']."' ondblclick='edit_item(".$dato['id'].", \"cantidad_".$dato['id']."\")' onfocusout='save_item(".$dato['id'].",2)'>";
                        }else{
                                 echo $dato['cantidad']; 
                                 
                        }
                        ?>
                        <td><?php echo $dato['almacen'];?></td>
                        <td><?php echo $dato['ubicacion']; ?></td>
                        <td>
                <button type="button" class="col-md-6 btn btn-block btn btn-primary botones_cel" data-bs-toggle="modal" data-bs-target="#editar_ubicacion" onclick="editarUbicacion(<?php echo $dato['id']; ?>, '<?php echo htmlspecialchars($dato['ubicacion'], ENT_QUOTES, 'UTF-8'); ?>')">EDITAR UBICACION</button>
                          <?php if($is_admin == 1|| $is_admin == 2){
                            $id_producto=$dato['id'];
                            echo "";
                           }
                             }?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <script>
            var tabla = document.querySelector("#tabla");
            var dataTable = new DataTable(tabla);
            </script>
            <?php
   
    ?>
        </div>
    </div>
<?php
   include 'modal/editar_ubicacion.php';
   include 'includes/footer.php'; ?>
    <script src="js/detalle_almacenes.js?v=<?=time()?>"></script>
    <script src="js/ventana_centrada.js?v=<?=time()?>"></script>
    <script>


function edit_item(id,item){

  var element=document.getElementById(item);
if (item == 'referencia_'+id){
  element.removeAttribute('readonly');
  console.log('editando '+item+' id '+id);
}else if(item == 'lote_'+id){
  document.getElementById('lote_'+id).removeAttribute('readonly');
}else if(item == 'caducidad_'+id){
  document.getElementById('caducidad_'+id).removeAttribute('readonly');
}else if(item == 'cantidad_+id'){
    document.gerElementById('cantidad_'+id).removeAttribute('readonly');
}


}


function save_item(id,select){
  
switch (select){

case 0:
  document.getElementById('referencia_'+id).setAttribute('readonly',true);
  var item = document.getElementById('referencia_'+id).value;
  var tipo = 'referencia';
  
break;

case 1:

  document.getElementById('lote_'+id).setAttribute('readonly',true);
  var item = document.getElementById('lote_'+id).value;
  var tipo = 'lote';
break;

case 2:

  document.getElementById('cantidad_'+id).setAttribute('readonly',true);
  var item = document.getElementById('cantidad_'+id).value;
  var tipo = 'cantidad';
break;


case 4:

document.getElementById('caducidad_'+id).setAttribute('readonly',true);
var item = document.getElementById('caducidad_'+id).value;
var tipo = 'caducidad';
var tipo_dato = typeof item;
break;

default:
  console.log('estas aki'+select);
  console.log(typeof select);
  break;

}

  $.ajax({
    type: "GET",
    url: "./ajax/editar_productos_ajax.php",
    data: "id="+id+"&tipo="+tipo+"&item="+item,
     beforeSend: function(objeto){
        $("#resultados").html("Mensaje: Cargando...");
      },
    success: function(datos){
    $("#resultados").html(datos);
    }
        });
  
}

    </script>
</body>

</html>