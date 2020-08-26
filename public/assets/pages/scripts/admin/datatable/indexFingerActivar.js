$(document).ready(function() {
    $("#myTable").on('submit', '.form-activar', function() {
        event.preventDefault();
        const form = $(this);
        swal({
            title: '¿Está seguro que deseas activar el registro?',
            text: "¡Esta acción puede ser revertida!",
            icon: 'warning',
            buttons: {
                cancel: "Cancelar",
                confirm: "Aceptar"
            },
        }).then((value) => {
            if (value) {
                ajaxRequest(form);
            }
        });
    });

    function ajaxRequest(form) {
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(respuesta) {
                if (respuesta.mensaje == "ok") {
                    form.parents('tr').remove();
                    Checador.notificaciones('El registro fue eliminado correctamente', 'Checador', 'success');
                } else {
                    Checador.notificaciones('El registro no pudo ser eliminado, hay recursos usandolo', 'Checador', 'error');
                }
            },
            error: function() {

            }
        });
    }
});