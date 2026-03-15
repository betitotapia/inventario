<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/head.php'; ?>
    <title>Traspaso entre almacenes</title>
    <style>
        .pointer { cursor:pointer; }
    </style>
</head>
<body>
    <div style="margin-bottom:5%;">
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/inventario/includes/navbar.php';?>
</div>
<div class="container mt-4">
    <h3>Traspaso entre almacenes</h3>

    <!-- Datos generales del traspaso -->
    <form id="form-traspaso">
        <div class="row g-3 mt-2">
            <div class="col-md-3">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" id="fecha" class="form-control" required
                       value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Almacén origen</label>
                <input type="number" name="almacen_origen" id="almacen_origen" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Almacén destino</label>
                <input type="number" name="almacen_destino" id="almacen_destino" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Folio de movimiento</label>
                <input type="text" name="folio" id="folio" class="form-control" required>
            </div>
        </div>

        <hr class="my-4">

        <!-- Buscador de productos -->
        <h5>Agregar productos al traspaso</h5>
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
                <label class="form-label">Escanear código de barras / QR</label>
                <input type="text" id="scan_codigo" class="form-control" placeholder="Escanea aquí (01..., 113..., etc.)">
                <small class="text-muted"></small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Buscar por referencia</label>
                <input type="text" id="buscar_referencia" class="form-control" placeholder="Referencia">
            </div>
            <div class="col-md-3">
                <label class="form-label">Buscar por lote</label>
                <input type="text" id="buscar_lote" class="form-control" placeholder="Lote">
            </div>
            <div class="col-md-2">
                <button type="button" id="btn-buscar" class="btn btn-primary w-100">
                    Buscar
                </button>
            </div>
        </div>

        <!-- Resultados de búsqueda -->
        <div class="table-responsive mb-4">
            <table class="table table-sm table-striped" id="tabla-resultados">
                <thead>
                    <tr>
                        <th>Referencia</th>
                        <th>Código</th>
                        <th>Lote</th>
                        <th>Caducidad</th>
                        <th>Almacén</th>
                        <th>Cantidad disponible</th>
                        <th>Ubicación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llena por AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Detalle del traspaso -->
        <h5>Detalle del traspaso</h5>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered" id="tabla-detalle">
                <thead>
                    <tr>
                        <th>Referencia</th>
                        <th>Código</th>
                        <th>Lote</th>
                        <th>Caducidad</th>
                        <th>Almacén origen</th>
                        <th>Almacén destino</th>
                        <th>Cantidad a traspasar</th>
                        <th>Quitar</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se agregan las filas seleccionadas -->
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <button type="button" id="btn-realizar" class="btn btn-success">
                Realizar traspaso
            </button>

            <!-- Carga por Excel/CSV -->
           
        </div>
    </form>

    <!-- Área oculta para generar PDF/impresión -->
    <div id="reporte-traspaso" class="d-none"></div>
</div>
<div class="container mt-4">
<div class="row g-3 mt-2">
            <div class="col-md-8">
                
                <label class="form-label me-2">Importar desde Excel/CSV:</label>
                <form id="form-excel" class="d-inline" method="post" enctype="multipart/form-data"
                      action="importar_traspasos_excel.php">
                    <input type="file" name="archivo_excel" accept=".csv,.xls,.xlsx" required>
                    <button type="submit" class="btn btn-secondary btn-sm" style='display: inline-block;'>Procesar archivo</button>
                </form>
                <div>
                    Descargar archivo de ejemplo: <a href="ejemplo_traspaso.csv" download>ejemplo_traspaso.csv</a>
                </div>
           
            </div>
        </div>
</div>
<script>
let detalleTraspaso = []; // arreglo en JS con las líneas del traspaso

// ===============================
// ESCÁNER (Código de barras / QR)
// Misma lógica base de js/script.js (01/17/10 y 113/17/10)
// ===============================
let gs1Codigo01 = null;

function parseScanToCodigoYLote(raw) {
    const codigo = (raw || '').trim();
    if (!codigo) return { ok: false, mensaje: 'Código vacío' };

    // 1) Etiqueta SUMED: 113 + (ref 16) + 17 + YYMMDD + 10 + LOTE
    if (codigo.startsWith('113') && codigo.length >= 29) {
        try {
            const codigoProducto = codigo.substring(0, 19); // 113 + 16
            const cad = codigo.substring(21, 27);          // después de '17'
            const lote = codigo.substring(29);             // después de '10'
            return { ok: true, tipo: 'sumed', codigo: codigoProducto, lote, caducidad: cad };
        } catch (e) {
            return { ok: false, mensaje: 'Formato SUMED inválido' };
        }
    }

    // 2) GS1 completo en una sola lectura: 01 + GTIN14 + 17 + YYMMDD + 10 + LOTE
    if (codigo.startsWith('01') && codigo.length >= 30) {
        try {
            const codigoProducto = codigo.substring(0, 16); // 01 + GTIN14
            const idx17 = codigo.indexOf('17', 16);
            const idx10 = codigo.indexOf('10', 16);
            if (idx17 === -1 || idx10 === -1) {
                return { ok: true, tipo: 'gs1', codigo: codigoProducto, lote: null, caducidad: null };
            }
            const cad = codigo.substring(idx17 + 2, idx17 + 8); // YYMMDD
            const lote = codigo.substring(idx10 + 2);
            return { ok: true, tipo: 'gs1', codigo: codigoProducto, lote, caducidad: cad };
        } catch (e) {
            return { ok: false, mensaje: 'Formato GS1 inválido' };
        }
    }

    // 3) GS1 separado en 2 lecturas (primero 01..., luego 17...10...)
    if (codigo.startsWith('01') && codigo.length >= 16) {
        gs1Codigo01 = codigo.substring(0, 16);
        return { ok: false, mensaje: 'Ahora escanea el 17...10... (caducidad/lote)' };
    }
    if (codigo.startsWith('17') && gs1Codigo01) {
        try {
            const idx10 = codigo.indexOf('10', 0);
            if (idx10 === -1) return { ok: false, mensaje: 'Falta AI 10 (lote) en el 17...' };
            const cad = codigo.substring(2, 8);
            const lote = codigo.substring(idx10 + 2);
            const out = { ok: true, tipo: 'gs1_2pasos', codigo: gs1Codigo01, lote, caducidad: cad };
            gs1Codigo01 = null;
            return out;
        } catch (e) {
            gs1Codigo01 = null;
            return { ok: false, mensaje: 'Formato 17... inválido' };
        }
    }

    // 4) QR simple (ej: REF|LOTE o CODIGO|LOTE)
    if (codigo.includes('|') || codigo.includes(';') || codigo.includes(',')) {
        const parts = codigo.split(/\||;|,/).map(x => x.trim()).filter(Boolean);
        if (parts.length >= 2) {
            return { ok: true, tipo: 'split', codigo: parts[0], lote: parts[1], caducidad: null };
        }
    }

    // 5) Código directo
    return { ok: true, tipo: 'directo', codigo, lote: null, caducidad: null };
}

function buscarYAgregarPorCodigo(rawScan) {
    const almacenOrigen = $('#almacen_origen').val().trim();
    const almacenDestino = $('#almacen_destino').val().trim();

    if (!almacenOrigen) {
        alert('Primero indica el almacén origen.');
        return;
    }
    if (!almacenDestino) {
        alert('Primero indica el almacén destino.');
        return;
    }

    const parsed = parseScanToCodigoYLote(rawScan);
    if (!parsed.ok) {
        if (parsed.mensaje) {
            // Mensaje suave (sin bloquear) + consola
            console.log(parsed.mensaje);
        }
        return;
    }

    $.ajax({
        url: './ajax/buscar_producto_codigo.php',
        type: 'GET',
        dataType: 'json',
        data: {
            almacen_origen: almacenOrigen,
            codigo: parsed.codigo,
            lote: parsed.lote || ''
        },
        success: function (resp) {
            if (!Array.isArray(resp) || resp.length === 0) {
                alert('No se encontró producto en almacén origen con ese código/lote. Primero debes darlo de alta en ese almacén.');
                return;
            }

            if (resp.length === 1) {
                const p = resp[0];
                const cantDisp = parseInt(p.cantidad, 10);
                const cantidad = prompt(`Cantidad a traspasar (máximo ${cantDisp}):`, '1');
                const cantNum = parseInt(cantidad, 10);

                if (!cantidad || isNaN(cantNum) || cantNum <= 0) {
                    alert('Cantidad no válida.');
                    return;
                }
                if (cantNum > cantDisp) {
                    alert('La cantidad no puede ser mayor a la existencia.');
                    return;
                }

                detalleTraspaso.push({
                    id_producto_origen: p.id,
                    referencia: p.referencia,
                    codigo: p.codigo,
                    lote: p.lote,
                    caducidad: p.caducidad,
                    almacen_origen: p.almacen,
                    almacen_destino: almacenDestino,
                    cantidad: cantNum
                });

                const tr = `
                    <tr data-id="${p.id}" data-lote="${p.lote}">
                        <td>${p.referencia ?? ''}</td>
                        <td>${p.codigo ?? ''}</td>
                        <td>${p.lote ?? ''}</td>
                        <td>${p.caducidad ?? ''}</td>
                        <td>${p.almacen}</td>
                        <td>${almacenDestino}</td>
                        <td>${cantNum}</td>
                        <td><button type="button" class="btn btn-sm btn-danger btn-quitar">X</button></td>
                    </tr>`;
                $('#tabla-detalle tbody').append(tr);
                return;
            }

            // Si vienen varios, los mostramos en la tabla de resultados para elegir
            const tbody = $('#tabla-resultados tbody');
            tbody.empty();
            resp.forEach(p => {
                const fila = `
                    <tr>
                        <td>${p.referencia ?? ''}</td>
                        <td>${p.codigo ?? ''}</td>
                        <td>${p.lote ?? ''}</td>
                        <td>${p.caducidad ?? ''}</td>
                        <td>${p.almacen}</td>
                        <td>${p.cantidad}</td>
                        <td>${p.ubicacion ?? ''}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-agregar"
                                data-id="${p.id}"
                                data-referencia="${p.referencia}"
                                data-codigo="${p.codigo}"
                                data-lote="${p.lote}"
                                data-caducidad="${p.caducidad}"
                                data-cantidad="${p.cantidad}"
                                data-almacen="${p.almacen}">
                                Agregar
                            </button>
                        </td>
                    </tr>`;
                tbody.append(fila);
            });
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert('Error al buscar por código.');
        }
    });
}

// Enter en el input de escaneo
$('#scan_codigo').on('keypress', function (e) {
    if (e.which === 13) {
        e.preventDefault();
        const raw = $(this).val();
        $(this).val('');
        buscarYAgregarPorCodigo(raw);
    }
});

// Buscar productos
$('#btn-buscar').on('click', function () {
    const ref = $('#buscar_referencia').val().trim();
    const lote = $('#buscar_lote').val().trim();
    const almacenOrigen = $('#almacen_origen').val().trim();

    if (!almacenOrigen) {
        alert('Primero indica el almacén origen.');
        return;
    }

    $.ajax({
        url: './ajax/buscar_productos.php',
        type: 'GET',
        dataType: 'json',
        data: {
            referencia: ref,
            lote: lote,
            almacen_origen: almacenOrigen
        },
        success: function (resp) {
            const tbody = $('#tabla-resultados tbody');
            tbody.empty();

            if (resp.length === 0) {
                tbody.append('<tr><td colspan="8" class="text-center">Sin resultados</td></tr>');
                return;
            }

            resp.forEach(p => {
                const fila = `
                    <tr>
                        <td>${p.referencia ?? ''}</td>
                        <td>${p.codigo ?? ''}</td>
                        <td>${p.lote ?? ''}</td>
                        <td>${p.caducidad ?? ''}</td>
                        <td>${p.almacen}</td>
                        <td>${p.cantidad}</td>
                        <td>${p.ubicacion ?? ''}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-agregar"
                                data-id="${p.id}"
                                data-referencia="${p.referencia}"
                                data-codigo="${p.codigo}"
                                data-lote="${p.lote}"
                                data-caducidad="${p.caducidad}"
                                data-cantidad="${p.cantidad}"
                                data-almacen="${p.almacen}">
                                Agregar
                            </button>
                        </td>
                    </tr>`;
                tbody.append(fila);
            });
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert('Error al buscar productos.');
        }
    });
});

// Al hacer clic en "Agregar" en los resultados
$(document).on('click', '.btn-agregar', function () {
    const almacenDestino = $('#almacen_destino').val().trim();
    if (!almacenDestino) {
        alert('Primero indica el almacén destino.');
        return;
    }

    const id = $(this).data('id');
    const ref = $(this).data('referencia');
    const cod = $(this).data('codigo');
    const lote = $(this).data('lote');
    const cad = $(this).data('caducidad');
    const cantDisp = parseInt($(this).data('cantidad'), 10);
    const almOrigen = $(this).data('almacen');

    const cantidad = prompt(`Cantidad a traspasar (máx ${cantDisp}):`, '1');
    const cantNum = parseInt(cantidad, 10);

    if (!cantidad || isNaN(cantNum) || cantNum <= 0) {
        alert('Cantidad no válida.');
        return;
    }
    if (cantNum > cantDisp) {
        alert('La cantidad no puede ser mayor a la existencia.');
        return;
    }

    // Guardamos en el arreglo
    detalleTraspaso.push({
        id_producto_origen: id,
        referencia: ref,
        codigo: cod,
        lote: lote,
        caducidad: cad,
        almacen_origen: almOrigen,
        almacen_destino: almacenDestino,
        cantidad: cantNum
    });

    // Pintamos en la tabla de detalle
    const tr = `
        <tr data-id="${id}" data-lote="${lote}">
            <td>${ref ?? ''}</td>
            <td>${cod ?? ''}</td>
            <td>${lote ?? ''}</td>
            <td>${cad ?? ''}</td>
            <td>${almOrigen}</td>
            <td>${almacenDestino}</td>
            <td>${cantNum}</td>
            <td><button type="button" class="btn btn-sm btn-danger btn-quitar">X</button></td>
        </tr>`;
    $('#tabla-detalle tbody').append(tr);
});

// Quitar línea del detalle
$(document).on('click', '.btn-quitar', function () {
    const fila = $(this).closest('tr');
    const id = fila.data('id');
    const lote = fila.data('lote');

    detalleTraspaso = detalleTraspaso.filter(
        x => !(x.id_producto_origen == id && x.lote == lote)
    );
    fila.remove();
});

// Realizar traspaso (envío a PHP)
$('#btn-realizar').on('click', function () {
    if (detalleTraspaso.length === 0) {
        alert('Agrega al menos un producto al traspaso.');
        return;
    }

    const datos = {
        fecha: $('#fecha').val(),
        almacen_origen: $('#almacen_origen').val(),
        almacen_destino: $('#almacen_destino').val(),
        folio: $('#folio').val(),
        detalle: JSON.stringify(detalleTraspaso)
    };

    $.ajax({
        url: './ajax/procesar_traspaso.php',
        type: 'POST',
        dataType: 'json',
        data: datos,
        success: function (resp) {
            if (!resp.ok) {
                alert('Error: ' + resp.mensaje);
                return;
            }

            alert('Traspaso realizado correctamente. Folio: ' + resp.folio);

            // Opcional: generar PDF/impresión con html2pdf
            $('#reporte-traspaso').html(resp.html_reporte).removeClass('d-none');
            const opt = {
                margin:       10,
                filename:     'traspaso_' + resp.folio + '.pdf',
            };
            html2pdf().from(document.getElementById('reporte-traspaso')).set(opt).save();

            // Limpiar detalle
            detalleTraspaso = [];
            $('#tabla-detalle tbody').empty();
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert('Error al procesar el traspaso.');
        }
    });
});
</script>
</body>
</html>
