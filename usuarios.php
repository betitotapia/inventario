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
    <h2>Usuarios</h2>
  </div>
  <div style="margin-left:85%;">
  <button class="btn-check"  name="usuarios" id="sumar"  autocomplete="off" ></button>
  <label class="btn btn-info btn-lg" for="usuarios" onclick="window.location.href='agregar_usuario.php'"><i class="bi bi-plus-circle"></i> Agregar Usuario</label>
  </div>
  <div class="container-fluid">
      <div class="card-body p-0 outer_div" id="tabla-completa" >
       
      </div>
    </div>

  <!-- Modal: Editar usuario -->
  <div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" id="modalEditarUsuarioBody">
          <div class="text-center py-4">Cargando...</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Cambiar contraseña -->
  <div class="modal fade" id="modalPasswordUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cambiar contraseña</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" id="modalPasswordUsuarioBody">
          <div class="text-center py-4">Cargando...</div>
        </div>
      </div>
    </div>
  </div>

  
  <?php
   include 'includes/footer.php'; ?>
  <script src="js/usuarios.js?v=<?=time()?>"></script>
</body>
</html>