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

$stmt = $pdo->prepare("SELECT id_usuario, nombre, username FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$u) {
    echo '<div class="alert alert-warning">No se encontró el usuario.</div>';
    exit;
}
?>

<form onsubmit="return guardarPasswordUsuario(this)">
  <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">

  <div class="mb-2">
    <div><strong><?= htmlspecialchars($u['nombre'] ?? '') ?></strong></div>
    <div class="text-muted"><?= htmlspecialchars($u['username'] ?? '') ?></div>
  </div>

  <div class="mb-3">
    <label class="form-label">Nueva contraseña</label>
    <input type="password" class="form-control" name="password" minlength="6" required>
    <div class="form-text">Mínimo 6 caracteres.</div>
  </div>

  <div class="mb-3">
    <label class="form-label">Confirmar contraseña</label>
    <input type="password" class="form-control" name="password2" minlength="6" required>
  </div>

  <div class="d-flex justify-content-end gap-2">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="submit" class="btn btn-warning">
      <i class="bi bi-key"></i> Actualizar
    </button>
  </div>
</form>
