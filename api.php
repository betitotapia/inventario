<?php
header("Content-Type: application/json");

// Conexión a la base de datos
$host = "162.241.61.215";
$db = "mimexiko_inve";
$user = "mimexiko_sumed";
$pass = "Sumed.2023";

$almacen= $_GET['almacen'] ?? '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Verifica qué endpoint se está solicitando
$endpoint = $_GET['endpoint'] ?? '';

switch ($endpoint) {
    case 'productos':
        $sql = "SELECT * FROM productos WHERE almacen = '$almacen' ";
        break;
    case 'almacenes':
        $sql = "SELECT * FROM almacenes";
        break;
    default:
        echo json_encode(["error" => "Endpoint no válido"]);
        exit;
}

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>
