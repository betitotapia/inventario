<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php'; // Protección de autenticación
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';

$user_id = $_SESSION['user_id'];
$tipo = $_GET['tipo'] ?? 'resumen';

// Datos del usuario (almacén y rol)
$usuario_almacen = $pdo->query("SELECT almacen, is_admin FROM usuarios WHERE id_usuario = ".$user_id."")->fetch(PDO::FETCH_ASSOC);
$id_almacen_usuario = $usuario_almacen['almacen'];
$is_admin = (int)$usuario_almacen['is_admin'];

if ($tipo === 'personalizado') {
    // Parámetros de búsqueda
    $fecha_inicio_raw = $_GET['fecha_inicio'] ?? '';
    $fecha_fin_raw    = $_GET['fecha_fin'] ?? '';
    $referencia       = trim($_GET['referencia'] ?? '');
    $lote             = trim($_GET['lote'] ?? '');

    // ---- Normalización de fechas ----
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

    // ---- Paginación ----
    $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    if ($limit <= 0) $limit = 50;
    if ($limit > 200) $limit = 200;

    // page (1-based) y/o offset directo
    $page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : ($page - 1) * $limit;

    // ---- Base de consulta con JOINs ----
    $sqlBase = "
        FROM movimientos m
        INNER JOIN productos p ON m.id_producto = p.id
        INNER JOIN usuarios u  ON m.id_usuario  = u.id_usuario
    ";

    // ---- WHERE dinámico ----
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

    // Filtro de fechas opcional
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

    // Restringir por almacén si no es admin
    if ($is_admin !== 1) {
        $where[] = "p.almacen = :almacen_usuario";
        $params[':almacen_usuario'] = $id_almacen_usuario;
    }

    $whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

    // ---- Conteo total para paginación ----
    $sqlCount = "SELECT COUNT(*) " . $sqlBase . " " . $whereSql;
    $stmtCount = $pdo->prepare($sqlCount);
    foreach ($params as $k => $v) {
        $stmtCount->bindValue($k, $v);
    }
    $stmtCount->execute();
    $total_rows = (int)$stmtCount->fetchColumn();
    $total_pages = ($limit > 0) ? (int)ceil($total_rows / $limit) : 1;

    // Si no hay datos, salir elegantemente
    if ($total_rows === 0) {
        echo "<p>No se encontraron movimientos con los criterios especificados.</p>";
        echo "<script>document.getElementById('btn_exportar_excel').style.display = 'none';</script>";
        exit;
    }

    // ---- Datos paginados ----
    $sqlData = "
        SELECT 
            m.id_movimiento, m.id_producto, m.cantidad, m.tipo_movimiento,
            m.id_usuario, m.date_created, m.campo_actualizado, m.cantidad_anterior, m.folio,
            p.id, p.codigo, p.referencia, p.lote, p.caducidad, p.cantidad AS existencia,
            p.almacen, p.ubicacion, p.ultima_modificacion,
            u.nombre
        " . $sqlBase . " 
        " . $whereSql . "
        ORDER BY m.date_created DESC, m.id_movimiento DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sqlData);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ---- Render de tabla ----
    echo "
<table id='tabla' class='table table-striped table-bordered'>
    <thead>
        <tr>
            <th>Código</th>
            <th>Referencia</th>
            <th>Lote</th>
            <th>Caducidad</th>
            <th>Tipo Movimiento</th>
            <th>Cantidad Actual</th>
            <th>Cantidad Anterior</th>
            <th>Almacén</th>
            <th>Campo Editado</th>
            <th>Ubicación</th>
            <th>Folio</th>
            <th>Fecha</th>
            <th>Usuario</th>
        </tr>
    </thead>
    <tbody>";

    foreach ($datos as $dato) { 
        switch ($dato['tipo_movimiento']) {
            case '1': $tipo_mov = 'Entrada';                    $color = 'green';     break;
            case '2': $tipo_mov = 'Salida';                     $color = 'red';       break;
            case '3': $tipo_mov = 'Ajuste de entrada';          $color = 'blue';      break;
            case '4': $tipo_mov = 'Ajuste de salida';           $color = '#d800d4';   break;
            case '5': $tipo_mov = 'Entrada por vale de préstamo'; $color = '#d89a00'; break;
            case '6': $tipo_mov = 'Salida por vale de préstamo';  $color = '#00d8d1'; break;
            case '8': $tipo_mov = 'Ajuste directo';             $color = '#06e2b6ff'; break;
            default:  $tipo_mov = 'Sin movimiento';             $color = 'black';     break;
        }

        $cantidad_anterior = $dato['cantidad_anterior'] ? $dato['cantidad_anterior'] : "";
        $campo_actualizado = $dato['campo_actualizado'] ? $dato['campo_actualizado'] : "";
        $nombre_usuario    = $dato['nombre'] ?? '';

        echo "
        <tr>
            <td>{$dato['codigo']}</td>
            <td>{$dato['referencia']}</td>
            <td>{$dato['lote']}</td>
            <td>{$dato['caducidad']}</td>
            <td style='background-color: {$color}; color:white;'>{$tipo_mov}</td>
            <td>{$dato['cantidad']}</td>
            <td>{$cantidad_anterior}</td>
            <td>{$dato['almacen']}</td>
            <td>{$campo_actualizado}</td>
            <td>{$dato['ubicacion']}</td>
            <td>{$dato['folio']}</td>
            <td>{$dato['date_created']}</td>
            <td>{$nombre_usuario}</td>
        </tr>";
    }

    echo "
    </tbody>
</table>
<script>
    var tabla = document.querySelector('#tabla');
    var dataTable = new DataTable(tabla);
</script>";

} else {
    echo "<p>No se encontraron movimientos para la referencia y lote especificados.</p>";
    echo "<script>document.getElementById('btn_exportar_excel').style.display = 'none';</script>";
    exit;
}
?>
