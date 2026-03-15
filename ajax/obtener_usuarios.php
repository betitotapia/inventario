<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

// Solo administradores pueden administrar usuarios
if (empty($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    http_response_code(403);
    echo '<div class="alert alert-danger">No tienes permisos para ver esta sección.</div>';
    exit;
}

$stmt = $pdo->query(
    "SELECT u.id_usuario, u.nombre, u.username, u.almacen, a.nombre_almacen, u.is_admin
     FROM usuarios u
     LEFT JOIN almacenes a ON a.id_almacen = u.almacen
     ORDER BY u.nombre ASC"
);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tipos_usuario = [
    1 => 'Administrador',
    2 => 'Gerente Almacén',
    3 => 'Coordinación Almacén',
    4 => 'Analista Almacén',
    5 => 'Operación Almacén',
    6 => 'Invitado'
];
?>

<table id="tablaUsuarios" class="table table-striped table-bordered align-middle">
  <thead>
    <tr>
      <th>Nombre</th>
      <th>Usuario</th>
      <th>Almacén asignado</th>
      <th>Tipo de usuario</th>
      <th style="width:220px;">Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['nombre'] ?? '') ?></td>
        <td><?= htmlspecialchars($u['username'] ?? '') ?></td>
        <td><?= htmlspecialchars($u['nombre_almacen'] ?? ('ID ' . ($u['almacen'] ?? ''))) ?></td>
        <td><?= htmlspecialchars($tipos_usuario[(int)($u['is_admin'] ?? 0)] ?? '—') ?></td>
        <td>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="abrirEditarUsuario(<?= (int)$u['id_usuario'] ?>)">
              <i class="bi bi-pencil-square"></i> Editar
            </button>
            <button type="button" class="btn btn-warning btn-sm" onclick="abrirPasswordUsuario(<?= (int)$u['id_usuario'] ?>)">
              <i class="bi bi-key"></i> Contraseña
            </button>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<script>
  (function(){
    var tabla = document.querySelector('#tablaUsuarios');
    if (tabla) new DataTable(tabla);
  })();
</script>
