<?php
header('Content-Type: application/json');

$servername = "162.241.61.215";
$username = "mimexiko_sumed";
$password = "Sumed.2023";
$dbname = "mimexiko_inve";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));

}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'generar') {
        $referencia = trim($_POST['referencia']);
        $lote = trim($_POST['lote']);
        $caducidad = trim($_POST['caducidad']);
        
        // Validar datos
        if (empty($referencia) || empty($lote) || empty($caducidad)) {
            echo json_encode(['error' => 'Todos los campos son obligatorios']);
            exit;
        }
        
        // Formatear caducidad a YYMMdd (6 caracteres)
        $caducidadFormatted = date('ymd', strtotime($caducidad));
        
        // Procesar la referencia para 16 caracteres exactos
        $referenciaFija = preg_replace('/\s+/', '', $referencia); // Eliminar todos los espacios
        $referenciaFija = substr($referenciaFija, 0, 16); // Cortar a 18 caracteres si es muy largo
        $referenciaFija = str_pad($referenciaFija, 16, '0', STR_PAD_RIGHT); // Rellenar con ceros
        
        // Construir código de barras completo
        $codigoBarras = '113' . $referenciaFija . '17' . $caducidadFormatted . '10' . $lote;
        
        // Guardar en base de datos
        $stmt = $conn->prepare("INSERT INTO etiquetas (referencia, lote, caducidad, codigo_barras) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $referencia, $lote, $caducidad, $codigoBarras);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'referencia' => $referencia,
                'codigo_barras' => $codigoBarras,
                'id' => $stmt->insert_id
            ]);
        } else {
            echo json_encode(['error' => 'Error al guardar en la base de datos: ' . $stmt->error]);
        }
        
        $stmt->close();
    }
    elseif ($action === 'reimprimir') {
        $id = intval($_POST['id']);
        
        $stmt = $conn->prepare("SELECT referencia, codigo_barras FROM etiquetas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'referencia' => $row['referencia'],
                'codigo_barras' => $row['codigo_barras'],
                'id' => $id
            ]);
        } else {
            echo json_encode(['error' => 'No se encontró la etiqueta solicitada']);
        }
        
        $stmt->close();
    } elseif ($action === 'buscar') {
        $query = '%' . trim($_POST['query']) . '%';
        
        $stmt = $conn->prepare("SELECT id, referencia, lote, caducidad, codigo_barras FROM etiquetas WHERE referencia LIKE ? OR lote LIKE ? OR codigo_barras LIKE ? ORDER BY fecha_creacion DESC LIMIT 50");
        $stmt->bind_param("sss", $query, $query, $query);
        $stmt->execute();
        $result = $stmt->get_result();
        $etiquetas = [];
        
        while ($row = $result->fetch_assoc()) {
            $etiquetas[] = $row;
        }
        
        echo json_encode(['success' => true, 'etiquetas' => $etiquetas]);
        $stmt->close();
    }
}

$conn->close();


?>