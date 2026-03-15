<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/head.php'; ?>
<body>
  <div style="margin-bottom:5%;">
  <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/navbar.php'; ?>
  </div>
  <div>
    <h2>Reporte</h2>
  </div>
  <div style="margin-left:85%;">
  <button class="btn-check"  name="usuarios" id="sumar"  autocomplete="off" ></button>
  <label class="btn btn-info btn-lg" for="usuarios" onclick="window.location.href='agregar_usuario.php'"><i class="bi bi-plus-circle"></i> Agregar Usuario</label>
  </div>
  <div class="col-md-2" style="margin-left: 2%;">
        <button type="button" class="btn btn-sm btn-success" onclick='descargar_excel();'>
            <span class="glyphicon glyphicon-download"></span> Descargar Excel</button>

    </div>
  <div class="container-fluid">
      <div class="card-body p-0 outer_div" id="tabla-completa" >
       
      </div>
    </div>

  
  <?php
   include 'includes/footer.php'; ?>
   
    <script src="js/ventana_centrada.js?v=<?=time()?>"></script>
  <script src="js/reportes.js?v=<?=time()?>"></script>
  
</body>
</html>