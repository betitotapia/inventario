function descargar_excel(id_almacen){
    VentanaCentrada('./reportes/reporte_almacenes.php?id_almacen='+id_almacen);

}
$( "#editar_ubicacion_producto" ).submit(function( event ) {
    $('#editar_ubicacion').attr("disabled", true);
    
   var parametros = $(this).serialize();
       $.ajax({
              type: "POST",
              url: "ajax/editar_ubicacion_ajax.php",
              data: parametros,
               beforeSend: function(objeto){
                  $("#resultados_ajax3").html("Mensaje: Cargando...");
                },
              success: function(datos){
              $("#resultados_ajax3").html(datos);
              location.reload();
              $('#actualizar_estado').attr("disabled", false);
              
            }
      });
      event.preventDefault();
    })
    function editarUbicacion(id,ubicacion){
        console.log(id+'el id');
        console.log(ubicacion+'la ubicacion');
         $("#id_mod").val(id);
         $("#id_ubicacion").val(ubicacion);

        
     }