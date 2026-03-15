<?php
// Configuración de la base de datos
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

try {
    // Conexión a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si se ha subido un archivo
    if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir el archivo.');
    }
    
    // Verificar que el archivo sea CSV
    $fileInfo = pathinfo($_FILES['csvFile']['name']);
    if (strtolower($fileInfo['extension']) !== 'csv') {
        throw new Exception('El archivo debe ser de tipo CSV.');
    }
    
    // Abrir el archivo CSV
    $csvFile = fopen($_FILES['csvFile']['tmp_name'], 'r');
    if ($csvFile === false) {
        throw new Exception('No se pudo abrir el archivo CSV.');
    }
    
    // Leer encabezados y verificar las columnas requeridas
    $headers = fgetcsv($csvFile);
    if ($headers === false) {
        throw new Exception('El archivo CSV está vacío o no tiene encabezados válidos.');
    }
    
    // Normalizar encabezados (minúsculas, sin espacios)
    $headers = array_map(function($header) {
        return strtolower(str_replace(' ', '', $header));
    }, $headers);
    
    // Columnas requeridas
    $requiredColumns = ['almacen', 'lote', 'caducidad', 'cantidad'];
    $missingColumns = array_diff($requiredColumns, $headers);
    
    if (!empty($missingColumns)) {
        throw new Exception('El archivo CSV no tiene las columnas requeridas. Faltan: ' . implode(', ', $missingColumns));
    }
    
    // Obtener índices de las columnas
    $almacenIndex = array_search('almacen', $headers);
    $loteIndex = array_search('lote', $headers);
    $caducidadIndex = array_search('caducidad', $headers);
    $cantidadIndex = array_search('cantidad', $headers);
    
    // Array para almacenar resultados
    $results = [];
    
    // Procesar cada línea del CSV
    while (($row = fgetcsv($csvFile)) !== false) {
        // Validar que la fila tenga datos
        if (empty(array_filter($row))) {
            continue;
        }
        
        // Obtener valores de la fila
        $almacen = trim($row[$almacenIndex]);
        $lote = trim($row[$loteIndex]);
        $caducidad = trim($row[$caducidadIndex]);
        $cantidad = (float) str_replace(',', '.', $row[$cantidadIndex]);
        
        // Validar cantidad
        if ($cantidad <= 0) {
            $results[] = [
                'referencia' => '',
                'lote' => $lote,
                'caducidad' => $caducidad,
                'cantidad' => $cantidad,
                'status' => 'Error: Cantidad debe ser mayor a cero',
                'class' => 'error'
            ];
            continue;
        }
        
        // Iniciar registro de resultado
        $resultEntry = [
            'referencia' => '',
            'lote' => $lote,
            'caducidad' => $caducidad,
            'cantidad' => $cantidad,
            'status' => '',
            'class' => ''
        ];
        
        // Buscar producto en el almacén original
        $producto = buscarProducto($pdo, $lote, $almacen);
        
        if ($producto) {
            // Actualizar cantidad (restar)
            $nuevaCantidad = $producto['cantidad'] - $cantidad;
            
            if ($nuevaCantidad < 0) {
                $resultEntry['status'] = 'Error: No hay suficiente stock en el almacén origen';
                $resultEntry['class'] = 'error';
                $results[] = $resultEntry;
                continue;
            }
            
            actualizarCantidadProducto($pdo, $producto['id'], $nuevaCantidad);
            registrarMovimiento($pdo, $producto['id'], -$cantidad,6, 1);
            
            $resultEntry['referencia'] = $producto['referencia'];
            $resultEntry['status'] = 'Producto encontrado en almacén origen. Stock actualizado.';
            $resultEntry['class'] = 'success';
            
            // Buscar producto en almacén 94
            $productoAlmacen94 = buscarProducto($pdo, $lote, '94');
            
            if ($productoAlmacen94) {
                // Actualizar cantidad (sumar)
                $nuevaCantidad94 = $productoAlmacen94['cantidad'] + $cantidad;
                actualizarCantidadProducto($pdo, $productoAlmacen94['id'], $nuevaCantidad94);
                registrarMovimiento($pdo, $productoAlmacen94['id'], $cantidad, 5, 1);
                
                $resultEntry['status'] .= ' Producto encontrado en almacén 94. Stock actualizado.';
            } else {
                // Buscar por lote para copiar datos
                $productoCopia = buscarProductoPorLote($pdo, $lote);
                
                if ($productoCopia) {
                    // Crear copia en almacén 94
                    $nuevoId = copiarProductoAlmacen94($pdo, $productoCopia, $cantidad, $caducidad);
                    registrarMovimiento($pdo, $nuevoId, $cantidad, 5, 1);
                    
                    $resultEntry['status'] .= ' Producto copiado al almacén 94.';
                } else {
                    $resultEntry['status'] .= ' Producto no encontrado en almacén 94. No se pudo copiar.';
                    $resultEntry['class'] = 'warning';
                }
            }
        } else {
            // Producto no encontrado en almacén origen
            $resultEntry['status'] = 'Producto no encontrado en el almacén origen';
            $resultEntry['class'] = 'error';
            
            // Buscar en almacén 94 por si acaso
            $productoAlmacen94 = buscarProducto($pdo, $lote, '94');
            
            if ($productoAlmacen94) {
                // Actualizar cantidad (sumar)
                $nuevaCantidad94 = $productoAlmacen94['cantidad'] + $cantidad;
                actualizarCantidadProducto($pdo, $productoAlmacen94['id'], $nuevaCantidad94);
                registrarMovimiento($pdo, $productoAlmacen94['id'], $cantidad, 5, 1);
                
                $resultEntry['referencia'] = $productoAlmacen94['referencia'];
                $resultEntry['status'] = 'Producto encontrado en almacén 94. Stock actualizado.';
                $resultEntry['class'] = 'success';
            } else {
                // Buscar por lote para copiar datos
                $productoCopia = buscarProductoPorLote($pdo, $lote);
                
                if ($productoCopia) {
                    // Crear copia en almacén 94
                    $nuevoId = copiarProductoAlmacen94($pdo, $productoCopia, $cantidad, $caducidad);
                    registrarMovimiento($pdo, $nuevoId, $cantidad,5, 1);
                    
                    $resultEntry['referencia'] = $productoCopia['referencia'];
                    $resultEntry['status'] = 'Producto copiado al almacén 94.';
                    $resultEntry['class'] = 'success';
                }
            }
        }
        
        $results[] = $resultEntry;
    }
    
    fclose($csvFile);
    
    // Generar tabla de resultados
    echo generarTablaResultados($results);
    
} catch (Exception $e) {
    echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

/**
 * Busca un producto por lote y almacén
 */
function buscarProducto($pdo, $lote, $almacen) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE lote = ? AND almacen = ?");
    $stmt->execute([$lote, $almacen]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Busca un producto solo por lote
 */
function buscarProductoPorLote($pdo, $lote) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE lote = ? LIMIT 1");
    $stmt->execute([$lote]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Actualiza la cantidad de un producto
 */
function actualizarCantidadProducto($pdo, $idProducto, $nuevaCantidad) {
    $stmt = $pdo->prepare("UPDATE productos SET cantidad = ?, ultima_modificacion = NOW() WHERE id = ?");
    $stmt->execute([$nuevaCantidad, $idProducto]);
}

/**
 * Registra un movimiento en la base de datos
 */
function registrarMovimiento($pdo, $idProducto, $cantidad, $tipoMovimiento, $idUsuario) {
    $stmt = $pdo->prepare("INSERT INTO movimientos (id_producto, cantidad, tipo_movimiento, id_usuario, date_created) 
                          VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$idProducto, $cantidad, $tipoMovimiento, $idUsuario]);
}

/**
 * Copia un producto al almacén 94 con la cantidad especificada
 * Ajustado para la estructura de la tabla: id, codigo, lote, caducidad, referencia, cantidad, almacen, ubicacion, ultima_modificacion
 */
function copiarProductoAlmacen94($pdo, $producto, $cantidad, $caducidad) {
    $stmt = $pdo->prepare("INSERT INTO productos 
                          (codigo, lote, caducidad, referencia, cantidad, almacen, ubicacion, ultima_modificacion) 
                          VALUES 
                          (:codigo, :lote, :caducidad, :referencia, :cantidad, '94', :ubicacion, NOW())");
    
    $stmt->execute([
        ':codigo' => $producto['codigo'],
        ':lote' => $producto['lote'],
        ':caducidad' => $caducidad, // Usamos la caducidad del CSV
        ':referencia' => $producto['referencia'],
        ':cantidad' => $cantidad,
        ':ubicacion' => isset($producto['ubicacion']) ? $producto['ubicacion'] : null
    ]);
    
    return $pdo->lastInsertId();
}

/**
 * Genera la tabla HTML con los resultados del procesamiento
 */
function generarTablaResultados($results) {
    if (empty($results)) {
        return '<p>No se encontraron registros para procesar.</p>';
    }
    
    $html = '<h2>Resultados del Procesamiento</h2>';
    $html .= '<table>';
    $html .= '<thead><tr>
                <th>Referencia</th>
                <th>Lote</th>
                <th>Caducidad</th>
                <th>Cantidad</th>
                <th>Status</th>
              </tr></thead>';
    $html .= '<tbody>';
    
    foreach ($results as $row) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($row['referencia']) . '</td>
                    <td>' . htmlspecialchars($row['lote']) . '</td>
                    <td>' . htmlspecialchars($row['caducidad']) . '</td>
                    <td>' . htmlspecialchars($row['cantidad']) . '</td>
                    <td class="' . htmlspecialchars($row['class']) . '">' . htmlspecialchars($row['status']) . '</td>
                 </tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Resumen
    $totalProcesados = count($results);
    $exitosos = count(array_filter($results, function($r) { return $r['class'] === 'success'; }));
    $advertencias = count(array_filter($results, function($r) { return $r['class'] === 'warning'; }));
    $errores = count(array_filter($results, function($r) { return $r['class'] === 'error'; }));
    
    $html .= '<div class="summary">
                <p><strong>Resumen:</strong> 
                Total procesados: ' . $totalProcesados . ' | 
                Éxitos: ' . $exitosos . ' | 
                Advertencias: ' . $advertencias . ' | 
                Errores: ' . $errores . '
                </p>
              </div>';
    
    return $html;
}
?>