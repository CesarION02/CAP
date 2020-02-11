$(document).on('change', '.check', function(e) {
    if (this.checked) {
        var departamento = $(e.currentTarget).val();
        var nombre = "#turno";
        var nombreDepartamento = "hid";
        var nombreDiv = "#tabla";
        nombreDiv = nombreDiv.concat(departamento);
        nombreDepartamento = nombreDepartamento.concat(departamento);
        nombreDepartamento = document.getElementById(nombreDepartamento).value;
        nombre = nombre.concat(departamento);
        $(nombre).val("0");
        $(nombre).attr('disabled', 'disabled');
        var tabla = '<table class="customers1"><tr><th COLSPAN="3">' + nombreDepartamento + '-Cerrado' + '</th></tr></table>';
        $(nombreDiv).empty("");
        $(nombreDiv).append(tabla);
    } else {
        var departamento = $(e.currentTarget).val();
        var nombre = "#turno";
        var nombreDepartamento = "hid";
        var nombreDiv = "#tabla";
        nombre = nombre.concat(departamento);
        nombreDiv = nombreDiv.concat(departamento);
        nombreDepartamento = nombreDepartamento.concat(departamento);
        nombreDepartamento = document.getElementById(nombreDepartamento).value;
        $(nombre).removeAttr('disabled', 'disabled');
        var tabla = '<table class="customers"><tr><th COLSPAN="3">' + nombreDepartamento + '</th></tr></table>';
        $(nombreDiv).empty("");
        $(nombreDiv).append(tabla);
    }
});