<?php
header('Content-Type: application/json');
$servername = "162.241.61.215";
$username = "mimexiko_sumed";
$password = "Sumed.2023";
$dbname = "mimexiko_inve";

$conn = new mysqli($servername, $username, $password, $dbname);
$query = "SELECT * FROM productos WHERE almacen =1";
$result = $conn->query($query);
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>