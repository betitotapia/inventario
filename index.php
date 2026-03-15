<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

 if ($_SESSION['is_admin'] == 6) {
                header('Location:almacenes.php');
            } 
?>
<!DOCTYPE html>
<html lang="es">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/head.php'; ?>
<body>
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/navbar.php'; ?>
  <br>
  <div class="container-fluid" style="margin-top:4%;">
    <div class="d-flex gap-3 mt-3">
      <input type="radio" class="btn-check" name="operacion" id="sumar" value="sumar" autocomplete="off" checked>
      <label class="btn btn-success btn-lg" for="sumar"><i class="bi bi-plus-circle"></i> SUMAR</label>

      <input type="radio" class="btn-check" name="operacion" id="restar" value="restar" autocomplete="off">
      <label class="btn btn-danger btn-lg" for="restar"><i class="bi bi-dash-circle"></i> RESTAR</label>
<?php
 $users=$pdo->query("SELECT * FROM usuarios where id_usuario='".$_SESSION['user_id']."'")->fetchAll(PDO::FETCH_ASSOC); 
foreach ($users as $user){
$is_admin=$user['is_admin'];
$usuario=$user['id_usuario'];
$no_almacen=$user['almacen'];

if ($is_admin==1 || $is_admin ==2  ){
   echo"   <input type='radio' class='btn-check' name='operacion' id='ajuste' value='ajuste' autocomplete='off'>
  <label class='btn btn-warning btn-lg' for='ajuste'><i class='bi bi-gear'></i>AJUSTE</label>
  ";}
}
  ?>
  
    </div>

    <div id="operacion_modo" style="margin-top:2%;width:15%;color:white;border: 2px; border-radius: 8px; padding: 5px;">
<br>
<p id="modo_operacion" style="margin-left:25%;font-size:18px;"></p> 
  </div>
    <br>
    <div class="input-group mb-3">
      <span class="input-group-text" id="basic-addon1"><i class="bi bi-upc-scan"></i></span>
      <input type="text" class="form-control" id="codigo" placeholder="ESCANEA EL CÓDIGO DE BARRAS" autofocus aria-label="Código de barras">
      </div>
      <br>
      <div id="modo" class="alert mt-2" role="alert" style="display: none;"></div>
    
      <div id="mensaje" class="alert mt-2" role="alert" style="display: none;"></div>
    
     <?php 
    $almacenes=$pdo->query("SELECT * FROM almacenes where id_almacen=$no_almacen ");
  foreach ($almacenes as $almacen) {
    $nombre_almacen=$almacen['nombre_almacen'];
  }
  ?>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
         <h3 class="mb-0">Estas agregando productos en Inventario <?php echo $no_almacen."-".$nombre_almacen;?> </h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cambiar_almacen">
              Cambiar de Almacen </button>
       
      </div>
      <div class="card-body p-0">
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
                </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
  
  <?php
   include 'includes/footer.php'; 
   include "modal/cambiar_almacen.php";
   ?>
  <script src="js/script.js?v=<?=time()?>"></script>
    <script>
    
</script>
</body>
</html>