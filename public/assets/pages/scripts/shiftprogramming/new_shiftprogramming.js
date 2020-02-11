function new_shiftprogramming() {
    var fechaini = document.getElementById("fechaini").value;
    var fechafin = document.getElementById("fechafin").value;
    var typearea = document.getElementById("typeArea").value;
    var listaEmpleados = "";
    var listaDepartamentos = "";
    $.ajax({
        type: 'get',
        url: 'newShift',
        data: { 'typearea': typearea },

        success: function(data) {
            var idJob = data[0][0].idJob;
            listaEmpleados += '<table class="customers"><tr><th>' + data[0][0].nameJob + '</th></tr>';
            for (var i = 0; data[0].length > i; i++) {
                if (idJob == data[0][i].idJob) {
                    listaEmpleados += '<tr><td>' + data[0][i].nameEmployee + '</td></tr>';
                } else {
                    listaEmpleados += '</table>';
                    idJob = data[0][i].idJob;
                    listaEmpleados += '<table class="customers"><tr><th>' + data[0][i].nameJob + '</th></tr>';
                    listaEmpleados += '<tr><td>' + data[0][i].nameEmployee + '</td></tr>';
                }

            }
            listaEmpleados += '</table><button id="botonCale" type="button" class="btn btn-warning" onclick="calendario()">Pasar Calendario</button>';

            var idDepartment = data[1][0].idDepartment;
            listaDepartamentos += crear_select_workshifts(data[1][0].idDepartment, data)
            listaDepartamentos += '<input type="hidden" id="hid' + data[1][0].idDepartment + '" value="' + data[1][0].nameDepartment + '">'
            listaDepartamentos += '<div type="row" id="tabla' + data[1][0].idDepartment + '"><table class="customers"><tr><th COLSPAN="3">' + data[1][0].nameDepartment + '</th></tr>';
            for (var i = 0; data[1].length > i; i++) {
                if (idDepartment == data[1][i].idDepartment) {
                    listaDepartamentos += '<tr><th COLSPAN="3">' + data[1][i].nameJob + '</th></tr>';

                } else {
                    listaDepartamentos += '</table></div>';
                    idDepartment = data[1][i].idDepartment;
                    listaDepartamentos += crear_select_workshifts(data[1][i].idDepartment, data)
                    listaDepartamentos += '<input type="hidden" id="hid' + data[1][i].idDepartment + '" value="' + data[1][i].nameDepartment + '">'
                    listaDepartamentos += '<div type="row" id="tabla' + data[1][i].idDepartment + '"><table class="customers"><tr><th COLSPAN="3">' + data[1][i].nameDepartment + '</th></tr>';
                    listaDepartamentos += '<tr><th COLSPAN="3">' + data[1][i].nameJob + '</th></tr>';


                }
            }
            listaDepartamentos += '</table></div>';
            $("#listanueva").empty("");
            $("#turnonuevo").empty("");
            $("#guardar").empty("")
                //$("#Antigua").empty(" ");
            $("#listanueva").append(listaEmpleados);
            $("#turnonuevo").append(listaDepartamentos);
            $("#guardar").append('<button class="btn btn-warning" id="guardar" name="guardar" onclick="guardar();">Guardar</button>');


        },
        error: function() {
            console.log('falle');
        }
    });
}

function crear_select_empleados(departamento, turno, renglon, job, data) {
    var selectEmpleados = '<select style="width: 80%" class="sel" name="sel' + ',d' + departamento + ',t' + turno + ',r' + renglon + ',p' + job + '" id="select' + 'd' + departamento + 't' + turno + 'r' + renglon + 'p' + job + '"><option value="0">Seleccione empleado</option>';

    for (var j = 0; data.length > j; j++) {
        selectEmpleados += '<option value="' + data[j].idEmployee + '" >' + data[j].nameEmployee + '</option>';
    }
    selectEmpleados += '</select>';
    return selectEmpleados;
}

function crear_select_workshifts(department, data) {
    var selectShift = '<div type="row" ><div class="col-md-6" style="margin:10px"><select name="turno' + department + '" id="turno' + department + '" class="turno"><option value="0">Seleccione horarios</option>';

    for (var j = 0; data[2].length > j; j++) {
        selectShift += '<option value="' + data[2][j].idShift + '" >' + data[2][j].nameShift + '</option>';
    }
    selectShift += '</select></div>';
    selectShift += '<div class="col-md-2" style="margin:10px"><input type="checkbox" name="cerrar' + department + '" id="cerrar' + department + '" value="' + department + '" class="check"> Cerrar </div></div>';
    return selectShift;
}