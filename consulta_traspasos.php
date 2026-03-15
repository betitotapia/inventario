<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/head.php'; ?>
    <title>Consulta de traspasos</title>
</head>
<body>
    <div style="margin-bottom:5%;">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/navbar.php';?>
</div>
<div class="container mt-4">
    <h3>Consulta de traspasos entre almacenes</h3>

    <!-- FILTROS -->
    <form id="form-filtros" class="mt-3 mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Fecha desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Almacén origen</label>
                <input type="number" id="almacen_origen" name="almacen_origen" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Almacén destino</label>
                <input type="number" id="almacen_destino" name="almacen_destino" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Folio</label>
                <input type="text" id="folio" name="folio" class="form-control">
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-end">
            <button type="button" id="btn-buscar" class="btn btn-primary me-2">
                Buscar
            </button>
            <button type="button" id="btn-limpiar" class="btn btn-secondary">
                Limpiar
            </button>
        </div>
    </form>

    <!-- TABLA RESULTADOS -->
    <div class="table-responsive">
        <table class="table table-sm table-striped" id="tabla-traspasos">
            <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Folio</th>
                <th>Almacén origen</th>
                <th>Almacén destino</th>
                <th>Usuario</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <!-- Se llena por AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL REPORTE / REIMPRESIÓN -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de traspaso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- aquí se inserta el reporte -->
                <div id="contenido-reporte"></div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-imprimir" class="btn btn-success">
                    Imprimir / PDF
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Default: mes actual
(function() {
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    document.getElementById('fecha_hasta').value = `${yyyy}-${mm}-${dd}`;
    document.getElementById('fecha_desde').value = `${yyyy}-${mm}-01`;
})();

// Buscar traspasos
function cargarTraspasos() {
    const datos = {
        fecha_desde: $('#fecha_desde').val(),
        fecha_hasta: $('#fecha_hasta').val(),
        almacen_origen: $('#almacen_origen').val(),
        almacen_destino: $('#almacen_destino').val(),
        folio: $('#folio').val()
    };

    $.ajax({
        url: './ajax/listar_traspasos.php',
        type: 'GET',
        dataType: 'json',
        data: datos,
        success: function(resp) {
            const tbody = $('#tabla-traspasos tbody');
            tbody.empty();

            if (!Array.isArray(resp) || resp.length === 0) {
                tbody.append('<tr><td colspan="7" class="text-center">Sin resultados</td></tr>');
                return;
            }

            resp.forEach(t => {
                const fila = `
                    <tr>
                        <td>${t.id}</td>
                        <td>${t.fecha}</td>
                        <td>${t.folio}</td>
                        <td>${t.almacen_origen}</td>
                        <td>${t.almacen_destino}</td>
                        <td>${t.usuario ?? ''}</td>
                        <td>
                            <button type="button"
                                class="btn btn-sm btn-outline-primary btn-ver"
                                data-id="${t.id}">
                                Ver / Reimprimir
                            </button>
                        </td>
                    </tr>`;
                tbody.append(fila);
            });
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            alert('Error al consultar traspasos.');
        }
    });
}

$('#btn-buscar').on('click', cargarTraspasos);

$('#btn-limpiar').on('click', function() {
    $('#form-filtros')[0].reset();
    // volvemos a poner fechas por defecto
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    $('#fecha_hasta').val(`${yyyy}-${mm}-${dd}`);
    $('#fecha_desde').val(`${yyyy}-${mm}-01`);
    cargarTraspasos();
});

// Al hacer clic en Ver / Reimprimir
$(document).on('click', '.btn-ver', function() {
    const id = $(this).data('id');

    $.ajax({
        url: './ajax/reporte_traspaso.php',
        type: 'GET',
        dataType: 'json',
        data: { id_traspaso: id },
        success: function(resp) {
            if (!resp.ok) {
                alert(resp.mensaje || 'No se pudo obtener el reporte.');
                return;
            }
            $('#contenido-reporte').html(resp.html);

            // guardar folio en data del botón para nombre de archivo
            $('#btn-imprimir').data('folio', resp.folio);

            const modal = new bootstrap.Modal(document.getElementById('modalReporte'));
            modal.show();
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            alert('Error al obtener el reporte.');
        }
    });
});

// Imprimir / generar PDF con html2pdf
$('#btn-imprimir').on('click', function() {
    const folio = $(this).data('folio') || 'traspaso';
    const elemento = document.getElementById('contenido-reporte');
    const opt = {
        margin: 10,
        filename: `traspaso_${folio}.pdf`
    };
    html2pdf().from(elemento).set(opt).save();
});

// Cargar listado inicial
$(document).ready(cargarTraspasos);
</script>
</body>
</html>
