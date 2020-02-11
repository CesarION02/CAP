$(document).on('change', '.turno', function(e) {
    var regex = /(\d+)/g;
    var turno = $(e.currentTarget).val();
    var selectdepartamento = $(e.currentTarget).attr('name');
    var departamento = selectdepartamento.match(regex);
    var typearea = document.getElementById("typeArea").value;
    var nombreDiv = "#tabla";
    var nombreDepartamento = "hid";
    nombreDepartamento = nombreDepartamento.concat(departamento);
    nombreDepartamento = document.getElementById(nombreDepartamento).value;
    nombreDiv = nombreDiv.concat(departamento);
    var listaTabla = "";
    var row = 0;

    $.ajax({
        type: 'get',
        url: 'workShift',
        data: { 'turno': turno, 'typearea': typearea, 'departamento': departamento },

        success: function(data) {
            $(nombreDiv).empty("");
            var numWork = data[1].length;
            var tamaño = 90 / numWork;
            listaTabla = '<table class="customers" id="t' + departamento + '"><tr><th COLSPAN="' + (numWork + 2) + '">' + nombreDepartamento + '</th></tr>';
            listaTabla += '<tr>';
            for (var i = 0; numWork > i; i++) {
                listaTabla += '<th width=' + tamaño + '%>' + data[1][i].nameWork + '</th>'
            }
            listaTabla += '<th width=9%></th></tr></table>';
            for (var i = 0; data[2].length > i; i++) {
                row = 1;
                listaTabla += '<table class="customers" id="tj' + data[2][i].idJob + '"><tr><th COLSPAN="' + (numWork) + '">' + data[2][i].nameJob + '</th><th><button type="button" class="btn btn-primary" onclick="agregarFila(' + departamento + ',' + data[2][i].idJob + ')"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button></th></tr><tr>';
                for (var j = 1; numWork >= j; j++) {
                    listaTabla += '<td>' + crear_select_empleados(departamento, data[1][j - 1].idWork, row, data[2][i].idJob, data[0]) + '</td>'
                }
                listaTabla += '<td class="boton"><button type="button" class="btn btn-danger" onclick="eliminarFila(' + row + ',' + data[2][i].idJob + ')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></td></tr>';
            }
            listaTabla += '</table>'
            $(nombreDiv).append(listaTabla);
            selectCheck();
        },
        error: function() {
            console.log('falle');
        }
    });

});