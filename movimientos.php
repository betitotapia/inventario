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
    <ul class="nav nav-tabs">
      <li class="nav-item">
        <button class="nav-link active" id="tab-resumen-tab" data-bs-toggle="tab" data-bs-target="#tab-resumen" type="button" role="tab">ULTIMOS MOVIMIENTOS</button>
     </li>
      <li class="nav-item">
          <button class="nav-link" id="tab-todo-tab" data-bs-toggle="tab" data-bs-target="#tab-todo" type="button" role="tab">Todos</button>
      </li>
      <li class="nav-item">
          <button class="nav-link" id="tab-personalizado-tab" data-bs-toggle="tab" data-bs-target="#tab-personalizado" type="button" role="tab">Busqueda </button>
      </li>

</ul>
  </div>
  <div class="container-fluid">
    
       <div class="tab-content mt-3">
          <div class="tab-pane fade show active" id="tab-resumen" role="tabpanel">
            <div id="contenido-resumen">Cargando...</div>
          </div>
          <div class="tab-pane fade" id="tab-todo" role="tabpanel">
            <div id="contenido-todo">Cargando...</div>
        </div>
        <div class="tab-pane fade" id="tab-personalizado" role="tabpanel">
            <div>
                <form id="form-personalizado" class="row g-3">
                    <div class="col-md-3">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_fin" class="form-label">Fecha Fin:</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                    </div>
                    <div class="col-md-3">
                        <label for="referencia" class="form-label">Referencia:</label>
                        <input type="text" class="form-control" id="referencia" name="referencia" >
                    </div>
                    <div class="col-md-3">
                        <label for="lote" class="form-label">Lote:</label>
                        <input type="text" class="form-control" id="lote" name="lote" >
                    </div>
                    <div class="col-md-3">
                        <button id="btn_buscar" type="submit" class="btn btn-primary">Buscar</button>
                   
    <button id="btn_exportar_excel" type="button" class="btn btn-success" style="display:inline-block;">
        <i class="bi bi-file-earmark-excel-fill"></i> Exportar a CSV/Excel
    </button>
</div>
                </form>
            </div>
            <div id="contenido-personalizado" class="mt-4">Cargando...</div>
        </div>

  
  <?php
   include 'includes/footer.php'; ?>
  <!--<script src="js/datatable.exportable.min.js" type="text/javascript"></script>-->
  <script src="js/movimientos.js?v=<?=time()?>"></script>

</body>
</html>