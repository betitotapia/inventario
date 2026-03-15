<?php
// Tu código PHP para procesar el CSV va aquí...
// (He omitido el bloque PHP por brevedad, pero debe estar aquí)
set_time_limit(1500);
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

session_start();
$id_usuario_actual = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 1; 

$resultados = []; // Asegurarse de que $resultados exista incluso si no hay archivo

if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] == 0) {
    $archivo = fopen($_FILES['archivo_csv']['tmp_name'], 'r');
    $resultados = [];

    // Saltar encabezado
    fgetcsv($archivo);

    while (($datos = fgetcsv($archivo, 1000, ",")) !== FALSE) {
        list($referencia, $lote, $cantidad, $almacen) = $datos;
        
        // Inicializar variables para el reporte
        $cantidad_anterior = 'N/A';
        $cantidad_actual = 'N/A';
        $estado = "No encontrado";
        
        // Busca el producto en la base de datos
        $stmt = $pdo->prepare("SELECT id, cantidad FROM productos WHERE referencia = ? AND lote = ? AND almacen = ?");
        $stmt->execute([$referencia, $lote, $almacen]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            try {
                // --- NUEVO: Iniciar transacción ---
                $pdo->beginTransaction();

                // 1. Actualizar la tabla de productos
                $cantidad_anterior = $producto['cantidad']; // Capturamos la cantidad antes de actualizar
                $cantidad_actual = $cantidad_anterior + $cantidad;
                
                $stmtUpdate = $pdo->prepare("UPDATE productos SET cantidad = ? WHERE id = ?");
                $stmtUpdate->execute([$cantidad_actual, $producto['id']]);
                
                // --- NUEVO: 2. Insertar el registro en la tabla de movimientos ---
                $stmtInsert = $pdo->prepare(
                    "INSERT INTO movimientos (id_producto, cantidad, tipo_movimiento, id_usuario, date_created, campo_actualizado, cantidad_anterior) 
                     VALUES (?, ?, ?, ?, NOW(), ?, ?)"
                );
                $stmtInsert->execute([
                    $producto['id'],
                    $cantidad,          // La cantidad que se restó del CSV
                    8,                  // Tipo de movimiento fijo, como solicitaste
                    $id_usuario_actual, // El ID del usuario en sesión
                    'cantidad',         // El campo que se actualizó
                    $cantidad_anterior  // La cantidad que había antes del UPDATE
                ]);

                // --- NUEVO: Si todo fue bien, confirmar la transacción ---
                $pdo->commit();
                $estado = "Actualizado";

            } catch (Exception $e) {
                // --- NUEVO: Si algo falló, revertir la transacción ---
                $pdo->rollBack();
                $estado = "Error: " . $e->getMessage();
            }
        }

        $resultados[] = [
            'referencia' => $referencia,
            'lote' => $lote,
            'cantidad' => $cantidad, // Cantidad restada
            'cantidad_anterior' => $cantidad_anterior,
            'cantidad_actual' => $cantidad_actual,
            'estado' => $estado
        ];
    }

    fclose($archivo);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado de la Carga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        @media print {
            body * {
                visibility: hidden; /* Oculta todo por defecto */
            }
            #area-imprimible, #area-imprimible * {
                visibility: visible; /* Muestra solo el área de impresión y sus hijos */
            }
            #area-imprimible {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none; /* Oculta los elementos con esta clase al imprimir */
            }
        }
    </style>
</head>
<body class="container mt-5">

    <div id="area-imprimible">
        <h2>Resultado de la carga</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Lote</th>
                    <th>Cantidad Restada</th>
                    <th>Cantidad Anterior</th>
                    <th>Cantidad Actual</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($resultados)): ?>
                    <?php foreach ($resultados as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['referencia']) ?></td>
                            <td><?= htmlspecialchars($fila['lote']) ?></td>
                            <td><?= htmlspecialchars($fila['cantidad']) ?></td>
                            <td><?= htmlspecialchars($fila['cantidad_anterior']) ?></td>
                            <td><?= htmlspecialchars($fila['cantidad_actual']) ?></td>
                            <td><?= htmlspecialchars($fila['estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No hay datos para mostrar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 no-print">
        <button id="btn-imprimir" class="btn btn-primary">🖨️ Imprimir</button>
        <button id="btn-generar-pdf" class="btn btn-success">📄 Generar PDF</button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
    // Funcionalidad para el botón de Imprimir
    document.getElementById('btn-imprimir').addEventListener('click', function () {
        window.print();
    });

    // Funcionalidad para el botón de Generar PDF
    document.getElementById('btn-generar-pdf').addEventListener('click', function () {
        // Seleccionar el elemento que queremos convertir a PDF
        const elemento = document.getElementById('area-imprimible');
        
        // --- OPCIONES CORREGIDAS ---
        const opt = {
          // 1. Establece un margen explícito para el PDF (ej. 0.5 pulgadas)
          margin:       0.5, 
          filename:     'resultado_inventario.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          // 2. ESTA ES LA CORRECCIÓN PRINCIPAL:
          //    Asegura que la captura del HTML comience desde la parte superior.
          html2canvas:  { 
              scale: 2,      // Mejora la resolución de la imagen
              scrollY: 0     // Evita que capture el espacio superior
          },
          jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        // Crear el PDF a partir del elemento con las nuevas opciones
        html2pdf().from(elemento).set(opt).save();
    });
</script>

</body>
</html>