<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

// Si usas composer:
$autoload = $_SERVER['DOCUMENT_ROOT'] . '/inventario/vendor/autoload.php';
if (!file_exists($autoload)) {
  http_response_code(500);
  echo "No se encontró vendor/autoload.php. Instala PhpSpreadsheet con: composer require phpoffice/phpspreadsheet";
  exit;
}
require_once $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$user_id = $_SESSION['user_id'] ?? 0;

// Datos del usuario (almacén y rol)
$usuario_almacen = $pdo->query("SELECT almacen, is_admin FROM usuarios WHERE id_usuario = " . (int)$user_id)->fetch(PDO::FETCH_ASSOC);
$id_almacen_usuario = $usuario_almacen['almacen'] ?? null;
$is_admin = (int)($usuario_almacen['is_admin'] ?? 0);

// Parámetros de búsqueda (mismos que tu buscador)
$fecha_inicio_raw = $_GET['fecha_inicio'] ?? '';
$fecha_fin_raw    = $_GET['fecha_fin'] ?? '';
$referencia       = trim($_GET['referencia'] ?? '');
$lote             = trim($_GET['lote'] ?? '');

// ---- Normalización de fechas (igual que obtener_movimientos_personalizado.php) ----
$fecha_inicio = '';
$fecha_fin    = '';

if ($fecha_inicio_raw !== '') {
  try {
    $dt = new DateTime($fecha_inicio_raw);
    $dt->setTime(0, 0, 0);
    $fecha_inicio = $dt->format('Y-m-d H:i:s');
  } catch (Exception $e) { $fecha_inicio = ''; }
}
if ($fecha_fin_raw !== '') {
  try {
    $dt = new DateTime($fecha_fin_raw);
    $dt->setTime(23, 59, 59);
    $fecha_fin = $dt->format('Y-m-d H:i:s');
  } catch (Exception $e) { $fecha_fin = ''; }
}

// ---- Base de consulta con JOINs (igual que tu buscador) ----
$sqlBase = "
  FROM movimientos m
  INNER JOIN productos p ON m.id_producto = p.id
  INNER JOIN usuarios  u ON m.id_usuario  = u.id_usuario
";

$where = [];
$params = [];

if ($referencia !== '') {
  $where[] = "p.referencia = :referencia";
  $params[':referencia'] = $referencia;
}
if ($lote !== '') {
  $where[] = "p.lote = :lote";
  $params[':lote'] = $lote;
}

// filtro fechas sobre movimientos.date_created
if ($fecha_inicio !== '' && $fecha_fin !== '') {
  $where[] = "m.date_created BETWEEN :fecha_inicio AND :fecha_fin";
  $params[':fecha_inicio'] = $fecha_inicio;
  $params[':fecha_fin']    = $fecha_fin;
} elseif ($fecha_inicio !== '') {
  $where[] = "m.date_created >= :fecha_inicio";
  $params[':fecha_inicio'] = $fecha_inicio;
} elseif ($fecha_fin !== '') {
  $where[] = "m.date_created <= :fecha_fin";
  $params[':fecha_fin'] = $fecha_fin;
}

// Restringir por almacén si no es admin (productos.almacen)
if ($is_admin !== 1) {
  $where[] = "p.almacen = :almacen_usuario";
  $params[':almacen_usuario'] = $id_almacen_usuario;
}

$whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

// ---- Datos (sin paginación) ----
$sqlData = "
  SELECT
    p.codigo,
    p.referencia,
    p.lote,
    p.caducidad,
    m.tipo_movimiento,
    m.cantidad,
    m.cantidad_anterior,
    p.almacen,
    m.campo_actualizado,
    p.ubicacion,
    m.folio,
    m.date_created,
    u.nombre
  " . $sqlBase . "
  " . $whereSql . "
  ORDER BY m.date_created DESC, m.id_movimiento DESC
";

$stmt = $pdo->prepare($sqlData);
foreach ($params as $k => $v) {
  $stmt->bindValue($k, $v);
}
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si no hay datos, no generamos archivo
if (!$data) {
  http_response_code(204); // No Content
  exit;
}

// ---- Crear Excel ----
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Movimientos');

// Encabezados (mismos que tu tabla)
$headers = [
  'Código','Referencia','Lote','Caducidad','Tipo Movimiento',
  'Cantidad Actual','Cantidad Anterior','Almacén','Campo Editado',
  'Ubicación','Folio','Fecha','Usuario'
];
$sheet->fromArray($headers, null, 'A1');

$rowNum = 2;

foreach ($data as $r) {
  // Mapear tipo_movimiento a texto (igual que tu switch)
  switch ((string)($r['tipo_movimiento'] ?? '')) {
    case '1': $tipo_mov = 'Entrada'; break;
    case '2': $tipo_mov = 'Salida'; break;
    case '3': $tipo_mov = 'Ajuste de entrada'; break;
    case '4': $tipo_mov = 'Ajuste de salida'; break;
    case '5': $tipo_mov = 'Entrada por vale de préstamo'; break;
    case '6': $tipo_mov = 'Salida por vale de préstamo'; break;
    case '8': $tipo_mov = 'Ajuste directo'; break;
    default:  $tipo_mov = 'Sin movimiento'; break;
  }

  $sheet->setCellValue("A{$rowNum}", $r['codigo'] ?? '');
  $sheet->setCellValue("B{$rowNum}", $r['referencia'] ?? '');
  $sheet->setCellValue("C{$rowNum}", $r['lote'] ?? '');
  $sheet->setCellValue("D{$rowNum}", $r['caducidad'] ?? '');
  $sheet->setCellValue("E{$rowNum}", $tipo_mov);
  $sheet->setCellValue("F{$rowNum}", $r['cantidad'] ?? '');
  $sheet->setCellValue("G{$rowNum}", $r['cantidad_anterior'] ?? '');
  $sheet->setCellValue("H{$rowNum}", $r['almacen'] ?? '');
  $sheet->setCellValue("I{$rowNum}", $r['campo_actualizado'] ?? '');
  $sheet->setCellValue("J{$rowNum}", $r['ubicacion'] ?? '');
  $sheet->setCellValue("K{$rowNum}", $r['folio'] ?? '');
  $sheet->setCellValue("L{$rowNum}", $r['date_created'] ?? '');
  $sheet->setCellValue("M{$rowNum}", $r['nombre'] ?? '');

  $rowNum++;
}

// Autosize columnas
foreach (range('A','M') as $col) {
  $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Descargar
$filename = 'movimientos_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;