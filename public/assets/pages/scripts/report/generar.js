function generar() {
    var fechaini = document.getElementById('start_date').value;
    var fechafin = document.getElementById('end_date').value;
    var type = document.getElementById('type').value;

    if (type == 1) {

    } else {
        var empleado = document.getElementById('empleado').value;
        $.ajax({
            type: 'get',
            url: 'generarReporteES',
            data: { 'empleado': empleado, 'fechaini': fechaini, 'fechafin': fechafin },

            success: function(data) {

            },
            error: function() {
                console.log('falle');
            }
        });
    }

}