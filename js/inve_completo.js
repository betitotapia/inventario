$(document).ready(function(){
    load(1);
});

function load(page){
    var q= $("#q").val();
    $("#loader").fadeIn('slow');
    $.ajax({
        url:'./ajax/obtener_registros_completo.php?action=ajax&page='+page+'&q='+q,
         beforeSend: function(objeto){
         $('#loader').html('<img src="./img/ajax-loader.gif"> Cargando...');
      },
        success:function(data){
            $(".outer_div").html(data).fadeIn('slow');
            $('#loader').html('');
            $('[data-toggle="tooltip"]').tooltip({html:true}); 
            
        }
    })
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