<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación 
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
// Obtener almacenes para el select
$stmt = $pdo->query("SELECT id_almacen, nombre_almacen FROM almacenes");
$almacenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tipos de usuario
$tipos_usuario = [
    1 => 'Administrador',
    2 => 'Gerente Almacén',
    3 => 'Coordinación Almacén',
    4 => 'Analista Almacén',
    5 => 'Operación Almacén',
    6 => 'Invitado'
];

?>

<!DOCTYPE html>
<html lang="es">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/head.php'; ?>
    <script>
    function validarFormulario(e) {
        const nombre = document.getElementById("nombre").value.trim();
        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value.trim();
        const almacen = document.getElementById("id_almacen").value;
        const tipo = document.getElementById("tipo_usuario").value;

        if (!nombre || !username || !password || !almacen || !tipo) {
            alert("Todos los campos son obligatorios.");
            e.preventDefault();
            return false;
        }

        if (password.length < 6) {
            alert("La contraseña debe tener al menos 6 caracteres.");
            e.preventDefault();
            return false;
        }

        return true;
    }

    function verificarUsuario() {
        const username = document.getElementById("username").value.trim();
        if (username.length > 2) {
            fetch('verificar_usuario.php?username=' + encodeURIComponent(username))
                .then(response => response.text())
                .then(data => {
                    const mensaje = document.getElementById("usuarioExiste");
                    if (data === "1") {
                        mensaje.textContent = "Este usuario ya existe.";
                        mensaje.classList.remove("text-success");
                        mensaje.classList.add("text-danger");
                    } else {
                        mensaje.textContent = "Usuario disponible.";
                        mensaje.classList.remove("text-danger");
                        mensaje.classList.add("text-success");
                    }
                });
        }
    }
    </script>
<body class="bg-light">
<div style="margin-bottom:5%;">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/navbar.php'; ?>
    </div>
<div class="container mt-5">
    <h3>Registrar Nuevo Usuario</h3>
    <form action="registrar.php" method="POST" class="p-4 bg-white shadow rounded" onsubmit="return validarFormulario(event)">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre completo</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Usuario</label>
            <input type="text" name="username" id="username" class="form-control" required onblur="verificarUsuario()">
            <div id="usuarioExiste" class="mt-1"></div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="id_almacen" class="form-label">Almacén</label>
            <select name="id_almacen" id="id_almacen" class="form-select" required>
                <option value="">Seleccione un almacén</option>
                <?php foreach ($almacenes as $almacen): ?>
                    <option value="<?= $almacen['id_almacen'] ?>"><?= htmlspecialchars($almacen['nombre_almacen']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="tipo_usuario" class="form-label">Tipo de Usuario</label>
            <select name="tipo_usuario" id="tipo_usuario" class="form-select" required>
                <option value="">Seleccione un tipo</option>
                <?php foreach ($tipos_usuario as $id => $nombre): ?>
                    <option value="<?= $id ?>"><?= htmlspecialchars($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Registrar Usuario</button>
    </form>
</div>
</body>
</html>
