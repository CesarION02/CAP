$(document).on('change', '#anio', function() {
    var anio = document.getElementById("anio").value;

    $.ajax({
        type: 'get',
        url: '../recoverHoliday',
        data: { 'anio': anio },

        success: function(data) {
            var aux = '<select id="semana" name="semana"><option value="0">Seleccione día festivo</option>'
            for (var i = 0; data.length > i; i++) {
                aux += '<option value="' + data[i].id + '">' + data[i].name + ' (' + order(data[i].fecha) + ') </option>'
            }
            aux += '</select>'

            $("#selectfestivo").empty("");
            $("#selectfestivo").append(aux);

        },
        error: function() {
            console.log('falle');
        }
    });
});

$(document).on('change', '#semana', function() {

    $("#date").prop("disabled", false);


});

$(document).on('change', '#date', function() {

    var anio = document.getElementById("anio").value;
    var fecha = document.getElementById("date").value;
    var res = fecha.split("-");

    if (res[0] > anio) {
        if (res[1] > 1) {
            swal("Error", "No se puede asignar día festivo fuera del año al que pertenece", "warning")
            $("#guardar").prop("disabled", true);
        } else {
            $("#guardar").prop("disabled", false);
        }
    } else if (res[0] < anio) {
        if (res[1] < 12) {
            swal("Error", "No se puede asignar día festivo fuera del año al que pertenece", "warning")
            $("#guardar").prop("disabled", true);
        } else {
            $("#guardar").prop("disabled", false);
        }
    } else {
        $("#guardar").prop("disabled", false);
    }

});

$(document).ready(function() {
    $("#guardar").prop("disabled", true);
});