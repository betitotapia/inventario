 $(document).ready(function () {
    cargarContenido('resumen');

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
      const targetId = $(e.target).attr('id');

      if (targetId === 'tab-resumen-tab') {
        cargarContenido('resumen');
      } else if (targetId === 'tab-todo-tab') {
        cargarContenido('todo');
      }
    });
$('#form-personalizado').on('submit', function(e) {
        // Previene la recarga de la página (comportamiento por defecto del submit)
        e.preventDefault(); 
        // Llama a la función para cargar los datos personalizados
        cargarContenido('personalizado');
        //$('#btn_exportar_excel').hide();
        // Opcional: Si el tab no está activo, actívalo
        $('#tab-personalizado-tab').tab('show');
    });


});


function cargarContenido(tab) {
    let url = '';
    let destino = '';

    var formData = new FormData(document.getElementById('form-personalizado'));
  

    if (tab === 'resumen') {
      url = './ajax/obtener_movimientos.php?tipo=resumen';
      destino = '#contenido-resumen';
    } else if (tab === 'todo') {
      url = './ajax/obtener_movimientos.php?tipo=todo';
      destino = '#contenido-todo';
    } else if (tab === 'personalizado') {
      url = './ajax/obtener_movimientos_personalizado.php?tipo=personalizado&' + new URLSearchParams(formData).toString();
      destino = '#contenido-personalizado';
    }else{
      return; // Salir si el tab no es reconocido
    }

   $.ajax({
      url: url,
      method: 'GET',
      beforeSend: function () {
        $(destino).html('Cargando...');
      },
      success: function (data) {
        $(destino).html(data);
     

      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("Error en la carga AJAX: ", textStatus, errorThrown);
        $(destino).html('<div class="alert alert-danger">Error al cargar los datos.</div>');
        
      }
    });
}



function editarProducto(id){
    console.log(id);
    $("#modal_editar").modal('show');
    $("#id_editar").val(id);
    $.ajax({
        url:'./ajax/obtener_registros_completo.php?action=ajax&id='+id,
        success:function(data){
            $("#datos_editar").html(data);
        }
    })
}
    // Exportar resultados del filtro (Excel/CSV)
   $(document).on('click', '#btn_exportar_excel', function (e) {
  e.preventDefault();

  const form = document.getElementById('form-personalizado');
  if (!form) return;

  const params = new URLSearchParams(new FormData(form));

  // Descarga directa del XLSX
  window.location.href = './ajax/exportar_movimientos_personalizado_xlsx.php?' + params.toString();
});
