<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Guardar la página actual para redirigir después del login
    // Redirigir al login
    header('Location:login.php');
    exit;
}



// Opcional: Verificar roles o permisos adicionales aquí
?>