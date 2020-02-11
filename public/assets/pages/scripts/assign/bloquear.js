$(document).on('change', '#departamento', function() {
    var valor = $("#departamento").val();
    if (valor == 0) {
        $("#empleado").removeAttr('disabled', 'disabled');
    } else {
        $("#empleado").attr('disabled', 'disabled');
    }

});

$(document).on('change', '#empleado', function() {
    var valor = $("#empleado").val();
    if (0 < valor.length) {
        $("#departamento").attr('disabled', 'disabled');
    } else {
        $("#departamento").attr('disabled', 'disabled');
    }

});