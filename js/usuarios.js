$(document).ready(function(){
    load(1);
});

function load(page){
    var q= $("#q").val();
    $("#loader").fadeIn('slow');
    $.ajax({
        url:'./ajax/obtener_usuarios.php?action=ajax&page='+page+'&q='+q,
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

// ===== Usuarios: Editar datos =====
async function abrirEditarUsuario(idUsuario){
    try {
        const modalEl = document.getElementById('modalEditarUsuario');
        const modalBody = document.getElementById('modalEditarUsuarioBody');
        modalBody.innerHTML = '<div class="text-center py-4">Cargando...</div>';

        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        const res = await fetch(`./ajax/form_editar_usuario.php?id_usuario=${encodeURIComponent(idUsuario)}`);
        const html = await res.text();
        modalBody.innerHTML = html;
    } catch (e) {
        console.error(e);
        alert('No se pudo cargar el formulario de edición.');
    }
}

async function guardarEdicionUsuario(formEl){
    try {
        const fd = new FormData(formEl);
        const res = await fetch('./ajax/actualizar_usuario.php', {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        if (!data.ok) {
            alert(data.message || 'No se pudo actualizar el usuario.');
            return false;
        }
        bootstrap.Modal.getInstance(document.getElementById('modalEditarUsuario'))?.hide();
        load(1);
        return false;
    } catch (e) {
        console.error(e);
        alert('Ocurrió un error al actualizar el usuario.');
        return false;
    }
}

// ===== Usuarios: Cambiar contraseña =====
async function abrirPasswordUsuario(idUsuario){
    try {
        const modalEl = document.getElementById('modalPasswordUsuario');
        const modalBody = document.getElementById('modalPasswordUsuarioBody');
        modalBody.innerHTML = '<div class="text-center py-4">Cargando...</div>';

        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        const res = await fetch(`./ajax/form_cambiar_password.php?id_usuario=${encodeURIComponent(idUsuario)}`);
        const html = await res.text();
        modalBody.innerHTML = html;
    } catch (e) {
        console.error(e);
        alert('No se pudo cargar el formulario de contraseña.');
    }
}

async function guardarPasswordUsuario(formEl){
    try {
        const fd = new FormData(formEl);
        const res = await fetch('./ajax/actualizar_password.php', {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        if (!data.ok) {
            alert(data.message || 'No se pudo actualizar la contraseña.');
            return false;
        }
        bootstrap.Modal.getInstance(document.getElementById('modalPasswordUsuario'))?.hide();
        alert('Contraseña actualizada.');
        return false;
    } catch (e) {
        console.error(e);
        alert('Ocurrió un error al actualizar la contraseña.');
        return false;
    }
}