<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

if (empty($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    http_response_code(403);
    echo '<div class="alert alert-danger">No tienes permisos para esta acción.</div>';
    exit;
}

$id_usuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : 0;
if ($id_usuario <= 0) {
    echo '<div class="alert alert-warning">Usuario inválido.</div>';
    exit;
}

$stmt = $pdo->prepare("SELECT id_usuario, nombre, username, almacen, is_admin FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$u) {
    echo '<div class="alert alert-warning">No se encontró el usuario.</div>';
    exit;
}

$almacenes = $pdo->query("SELECT id_almacen, nombre_almacen FROM almacenes ORDER BY nombre_almacen ASC")->fetchAll(PDO::FETCH_ASSOC);

$tipos_usuario = [
    1 => 'Administrador',
    2 => 'Gerente Almacén',
    3 => 'Coordinación Almacén',
    4 => 'Analista Almacén',
    5 => 'Operación Almacén',
    6 => 'Invitado'
];
?>

<form onsubmit="return guardarEdicionUsuario(this)">
  <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">

  <div class="mb-3">
    <label class="form-label">Nombre completo</label>
    <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($u['nombre'] ?? '') ?>" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Usuario</label>
    <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($u['username'] ?? '') ?>" required>
    <div class="form-text">* Debe ser único.</div>
  </div>

  <div class="mb-3">
    <label class="form-label">Almacén</label>
    <select class="form-select" name="id_almacen" required>
      <option value="">Seleccione un almacén</option>
      <?php foreach ($almacenes as $a): ?>
        <option value="<?= (int)$a['id_almacen'] ?>" <?= ((int)$u['almacen'] === (int)$a['id_almacen']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($a['nombre_almacen']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Tipo de usuario</label>
    <select class="form-select" name="tipo_usuario" required>
      <option value="">Seleccione un tipo</option>
      <?php foreach ($tipos_usuario as $id => $label): ?>
        <option value="<?= (int)$id ?>" <?= ((int)$u['is_admin'] === (int)$id) ? 'selected' : '' ?>>
          <?= htmlspecialchars($label) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="d-flex justify-content-end gap-2">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-save"></i> Guardar cambios
    </button>
  </div>
</form>
