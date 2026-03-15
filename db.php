<?php

// $host = '162.241.61.215';
// $db   = 'mimexiko_inve';
// $user = 'mimexiko_sumed';
// $pass = 'Sumed.2023';
// $charset = 'utf8mb4';

$host = 'localhost';
$db   = 'inventario';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
// Configuración de rutas

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Registrar error en un archivo log
    error_log("Error de conexión: " . $e->getMessage(), 3, ROOT_PATH . '/error.log');
    die("Error de conexión. Por favor intente más tarde.");
}
?>