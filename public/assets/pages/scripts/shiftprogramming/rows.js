function agregarFila(tabla, job) {
    var cadena = "tj";
    var turno = "turno";
    cadena = cadena.concat(job);
    turno = turno.concat(tabla);
    turno = document.getElementById(turno).value;
    var typearea = document.getElementById("typeArea").value;
    var listaTabla = "";
    var row = document.getElementById(cadena).rows.length;


    $.ajax({
        type: 'get',
        url: 'newRow',
        data: { 'turno': turno, 'typearea': typearea },

        success: function(data) {



            var numWork = data[0].length;

            for (var j = 1; numWork >= j; j++) {
                listaTabla += '<tr><td>' + crear_select_empleados(tabla, data[0][j - 1].idWork, row, job, data[1]) + '</td>'
            }
            listaTabla += '<td class="boton"><button type="button" class="btn btn-danger" onclick="eliminarFila(' + row + ',' + job + ')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></td></tr>';
            listaTabla += '</tr>';


            document.getElementById(cadena).insertRow(row).innerHTML = listaTabla;
        },
        error: function() {
            console.log('falle');
        }
    });


}

function eliminarFila(row, job) {
    var cadena = "tj";
    cadena = cadena.concat(job);
    var table = document.getElementById(cadena);
    table.deleteRow(row);
}