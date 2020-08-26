$(document).ready(function() {
    $("#myTable").on('submit', '.form-eliminar', function() {
        event.preventDefault();
        const form = $(this);
        swal({
            title: '¿Está seguro que desea enviar a foráneo?',
            text: "¡Esta acción se puede revertir!",
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

$(document).ready(function() {
    $("#myTable").on('submit', '.form-configurar', function() {
        event.preventDefault();
        const form = $(this);
        swal({
            title: '¿ Está seguro que desea confirmar depto. ?',
            text: "Esta acción se puede revertir!",
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