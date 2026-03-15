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
    <h2>Almacenes</h2>
  </div>
  <div class="container-fluid">
      <div class="card-body p-0 outer_div" id="tabla-completa" >
       
      </div>
    </div>

  
  <?php
   include 'includes/footer.php'; ?>
  <script src="js/almacenes.js?v=<?=time()?>"></script>
</body>
</html>