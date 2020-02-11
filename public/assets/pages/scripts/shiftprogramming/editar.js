function editShift() {
    var semana = document.getElementById("newest").value;
    var typearea = document.getElementById("typeArea").value;
    var listaEmpleados = "";
    var listaDepartamentos = "";
    var colspanD = 0;
    var auxWork = [];
    var auxIdJob = [];
    var auxNameJob = [];
    var auxEmpleado = [];
    var renglon = [];

    var contadorEmpleados = 0;


    $.ajax({
        type: 'get',
        url: 'editRol',
        data: { 'semana': semana, 'typearea': typearea },

        success: function(data) {
            document.getElementById("fechaini").value = data[6][0].start;
            document.getElementById("fechafin").value = data[6][0].end;
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

            var contadorJob = 0;
            var contadorWork = 0;
            for (var i = 0; data[2].length > i; i++) {
                colspanD = 1;
                for (var j = 0; data[4].length > j; j++) {
                    if (data[2][i].group == data[4][j].idShift) {
                        colspanD++;
                    }
                }
                for (var j = 0; data[5].length > j; j++) {
                    if (data[2][i].idDepart == data[5][j].idDepartment) {
                        auxIdJob[contadorJob] = data[5][j].idJob;
                        auxNameJob[contadorJob] = data[5][j].nameJob;
                        contadorJob++;
                    }
                }
                contadorJob = 0;
                if (data[2][i].status == 2) {
                    listaDepartamentos += '<div type="row" ><div class="col-md-4" style="margin:10px"><select disabled name="turno' + data[2][i].idDepart + '" id="turno' + data[2][i].idDepart + '" class="turno"><option value="0">Seleccione horarios</option>';
                    for (var j = 0; data[3].length > j; j++) {
                        listaDepartamentos += '<option value="' + data[3][j].idShift + '" >' + data[3][j].nameShift + '</option>';
                    }
                    listaDepartamentos += '</select></div>';
                    listaDepartamentos += '<div class="col-md-2" style="margin:10px"><input checked type="checkbox" name="cerrar' + data[2][i].idDepart + '" id="cerrar' + data[2][i].idDepart + '" value="' + data[2][i].idDepart + '" class="check"> Cerrar </div></div>';
                    listaDepartamentos += '<input type="hidden" id="hid' + data[2][i].idDepart + '" value="' + data[2][i].nameDepart + '">'
                    listaDepartamentos += '<div type="row" id="tabla' + data[2][i].idDepart + '"><table class="customers1"><tr><th COLSPAN="' + colspanD + '">' + data[2][i].nameDepart + '-Cerrado' + '</th></tr></table></div>'
                } else {
                    listaDepartamentos += crear_workshifts(data[2][i].idDepart, data, i)
                    listaDepartamentos += '<input type="hidden" id="hid' + data[2][i].idDepart + '" value="' + data[2][i].nameDepart + '">'
                    listaDepartamentos += '<div type="row" id="tabla' + data[2][i].idDepart + '"><table id="t' + data[2][i].idDepart + '" class="customers"><tr><th COLSPAN="' + colspanD + '">' + data[2][i].nameDepart + '</th></tr>';
                    for (var j = 0; data[4].length > j; j++) {
                        if (data[2][i].group == data[4][j].idShift) {
                            listaDepartamentos += '<th>' + data[4][j].nameWork + '</th>'
                            auxWork[contadorWork] = data[4][j].idWork;
                            contadorWork++;
                        }
                    }
                    contadorWork = 0;
                    listaDepartamentos += '<th></th></tr></table>';
                    for (var z = 0; auxIdJob.length > z; z++) {
                        renglon[contadorEmpleados] = "";
                        listaDepartamentos += '<table class="customers" id="tj' + auxIdJob[z] + '"><tr><th COLSPAN="' + (colspanD) + '">' + auxNameJob[z] + '</th><th><button type="button" class="btn btn-primary" onclick="agregarFila(' + data[2][i].idDepart + ',' + auxIdJob[z] + ')"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button></th></tr><tr>';
                        for (var x = 0; auxWork.length > x; x++) {

                            for (var y = 0; data[1].length > y; y++) {
                                if (data[1][y].idJob == auxIdJob[z] && data[1][y].id == auxWork[x] && data[1][y].idD == data[2][i].idDepart) {

                                    renglon[contadorEmpleados] += '<td>' + crear_empleados(data[2][i].idDepart, auxWork[x], contadorEmpleados + 1, auxIdJob[z], data[0], data[1][y].idEmployee) + '</td>'
                                    contadorEmpleados++;
                                    renglon[contadorEmpleados] = "";
                                }
                            }
                            contadorEmpleados = 0;

                        }
                        for (var h = 1; renglon.length > h; h++) {
                            listaDepartamentos += renglon[h - 1] + '<td class="boton"><button type="button" class="btn btn-danger" onclick="eliminarFila(' + h + ',' + auxIdJob[z] + ')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></td></tr>'
                        }
                        renglon = [];
                        listaDepartamentos += '</table></div>';
                    }


                }
                auxIdJob = [];
                auxNameJob = [];
                auxWork = [];
            }
            document.getElementById("Antigua").style.display = "none";

            tablinks = document.getElementsByClassName("tablinks");
            tablinks[0].className += " active";
            tablinks[1].className = tablinks[1].className.replace(" active", "");
            document.getElementById("Nueva").style.display = "block";
            document.getElementById("nuevo").onclick = null;
            $("#listanueva").empty("");
            $("#turnonuevo").empty("");
            $("#guardar").empty("")
            $("#listanueva").append(listaEmpleados);
            $("#turnonuevo").append(listaDepartamentos);
            $("#guardar").append('<button class="btn btn-warning" id="guardar" name="guardar" onclick="guardar();">Guardar</button>');
        },
        error: function() {
            console.log('falle');
        }
    });
}

function crear_empleados(departamento, turno, renglon, job, data, empleado) {
    var selectEmpleados = '<select style="width: 80%" class="sel" name="sel' + ',d' + departamento + ',t' + turno + ',r' + renglon + ',p' + job + '" id="select' + 'd' + departamento + 't' + turno + 'r' + renglon + 'p' + job + '"><option value="0">Seleccione empleado</option>';

    for (var j = 0; data.length > j; j++) {
        if (empleado == data[j].idEmployee) {
            selectEmpleados += '<option selected=selected value="' + data[j].idEmployee + '" >' + data[j].nameEmployee + '</option>';
        } else {
            selectEmpleados += '<option value="' + data[j].idEmployee + '" >' + data[j].nameEmployee + '</option>';
        }

    }
    selectEmpleados += '</select>';
    return selectEmpleados;
}

function crear_workshifts(department, data, i) {
    var selectShift = '<div type="row" ><div class="col-md-6" style="margin:10px"><select name="turno' + department + '" id="turno' + department + '" class="turno"><option value="0">Seleccione horarios</option>';

    for (var j = 0; data[3].length > j; j++) {
        if (data[2][i].group == data[3][j].idShift) {
            selectShift += '<option selected=selected value="' + data[3][j].idShift + '" >' + data[3][j].nameShift + '</option>';
        } else {
            selectShift += '<option value="' + data[3][j].idShift + '" >' + data[3][j].nameShift + '</option>';
        }

    }
    selectShift += '</select></div>';
    selectShift += '<div class="col-md-2" style="margin:10px"><input type="checkbox" name="cerrar' + department + '" id="cerrar' + department + '" value="' + department + '" class="check"> Cerrar </div></div>';
    return selectShift;
}