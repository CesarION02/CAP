$(document).ready(function() {
    $("#tabla-data").on('submit', '.form-eliminar', function() {
        event.preventDefault();
        const form = $(this);
        swal({
            title: '¿ Está seguro que desea eliminar el registro ?',
            text: "Esta acción no se puede deshacer!",
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
                }  else if (respuesta.mensaje == "cerrado") {
                    swal({
                        title: 'Aviso',
                        text: 'El registro no se puede eliminar porque la nómina esta cerrada.',
                        icon: 'info',
                        confirmButtonText: 'Aceptar'
                    })    
                } else {
                    Checador.notificaciones('El registro no pudo ser eliminado', 'Checador', 'error');
                }
            },
            error: function() {

            }
        });
    }
});

$(document).ready(function() {
    $("#tabla-data").on('submit', '.form-activar', function() {
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
                }  else if (respuesta.mensaje == "cerrado") {
                    swal({
                        title: 'Aviso',
                        text: 'El registro no se puede activar porque la nómina esta cerrada.',
                        icon: 'info',
                        confirmButtonText: 'Aceptar'
                    })    
                } else {
                    Checador.notificaciones('El registro no pudo ser eliminado', 'Checador', 'error');
                }
            },
            error: function() {

            }
        });
    }
});