function calendario() {
    var j = 0;
    var arrName = [];
    var arrValue = [];
    var arrTurno = [];
    var arrJob = [];
    var arrDept = [];
    var arrText = [];
    var todosTurnos = [];
    var nombreTurno = "";
    var entrada = "";
    var salida = "";
    var typearea = document.getElementById("typeArea").value;
    var text = "";
    var regex = "";
    var datestart = "";
    var datend = "";
    $('.sel').each(function() {
        if ($(this).val() != 0) {
            arrValue[j] = $(this).val();
            arrName[j] = this.name;
            arrText[j] = this.options[this.options.selectedIndex].text;
            regex = arrName[j].split(',');
            arrDept[j] = regex[1].substring(1);
            arrTurno[j] = regex[2].substring(1);
            arrJob[j] = regex[4].substring(1);
            j++;
            $(this).attr('disabled', true);
        }
    });

    var fechaini = document.getElementById("fechaini").value;
    var fechafin = document.getElementById("fechafin").value;
    if (fechaini == "" || fechafin == "") {
        swal("Error", "La fecha inicial o fecha final no pueden estar vacias", "warning")
    } else {
        datestart = fechaini.split('-');
        datend = fechafin.split('-');
        var iniyear = datestart[0];
        var inimonth = datestart[1] - 1;
        var iniday = datestart[2];
        var finyear = datend[0];
        var finmonth = datend[1] - 1;
        var finday = datend[2];
        var inicio = new Date(iniyear, inimonth, iniday).getTime();
        var fin = new Date(finyear, finmonth, finday).getTime();
        var inif = new Date(inicio);
        var finf = new Date(fin);
        var diaSemana = "";
        var dia = "";
        var diff = fin - inicio;
        var inicioDate = new Date(inicio);
        var auxiliarDate = new Date(inicio);
        var auxiliarDate2 = new Date(inicio);

        diff = (diff / (1000 * 60 * 60 * 24));
        var calendario = "";
        calendario += '<table class="customers"><tr>'

        for (var i = 0; diff >= i; i++) {
            diaSemana = inif.getDay();
            diaSemana = diasemana(diaSemana);
            calendario += '<th>' + diaSemana + '</th>'
            inif.setDate(inif.getDate() + 1);
        }
        calendario += '</tr><tr>';
        for (var i = 0; diff >= i; i++) {
            mes = inicioDate.getMonth();
            mes = meses(mes);
            dia = inicioDate.getDate();
            calendario += '<th>' + dia + ' - ' + mes + '</th>'
            inicioDate.setDate(inicioDate.getDate() + 1);
        }




        todosTurnos = arrTurno.unique();

        $.ajax({
            type: 'get',
            url: 'turnos',
            data: { 'typearea': typearea, 'ini': fechaini, 'fin': fechafin },

            success: function(data) {
                calendario += '</tr><tr>';
                var turno = data[0].idWork;
                for (var z = 0; todosTurnos.length > z; z++) {
                    for (var x = 0; data[0].length > x; x++) {
                        if (todosTurnos[z] == data[0][x].idWork) {
                            nombreTurno = data[0][x].nameWork;
                            entrada = data[0][x].entry;
                            salida = data[0][x].departure;
                        }
                    }
                    calendario += '<th COLSPAN="' + (diff + 1) + '">' + nombreTurno + ' ' + entrada + ' a ' + salida + '</th></tr>'
                    incidenciaFlag = 0;
                    for (var y = 0; arrTurno.length > y; y++) {
                        if (todosTurnos[z] == arrTurno[y]) {
                            calendario += '<tr>'

                            for (var h = 0; diff >= h; h++) {
                                incidenciaFlag = 0;
                                for (contadorVac = 0; data[1].length > contadorVac; contadorVac++) {
                                    if (data[1][contadorVac].idEmp == arrValue[y]) {
                                        var fechaComp = data[1][contadorVac].Date;
                                        var arrDate = fechaComp.split('-');
                                        iniyear = arrDate[0];
                                        inimonth = arrDate[1] - 1;
                                        iniday = arrDate[2];
                                        var comparacion = new Date(iniyear, inimonth, iniday).getTime();
                                        var datocomparado = new Date(comparacion);
                                        if (datocomparado.getTime() == auxiliarDate.getTime()) {
                                            var nombretd = 'col' + h + 'ren' + y + 'tur' + z + 'emp' + arrValue[y];
                                            calendario += '<td class="calendario" onclick="tdclick(' + h + ',' + y + ',' + z + ',' + arrValue[y] + ');" id=' + nombretd + '>VACACIONES</td>';
                                            contadorVac = 100;
                                            incidenciaFlag = 1;
                                        }

                                    }
                                }
                                if (incidenciaFlag != 1) {
                                    for (contadorInc = 0; data[2].length > contadorInc; contadorInc++) {
                                        if (data[2][contadorInc].idEmp == arrValue[y]) {
                                            fechaComp = data[2][contadorInc].Date;
                                            arrDate = fechaComp.split('-');
                                            iniyear = arrDate[0];
                                            inimonth = arrDate[1] - 1;
                                            iniday = arrDate[2];
                                            comparacion = new Date(iniyear, inimonth, iniday).getTime();
                                            datocomparado = new Date(comparacion);
                                            if (datocomparado.getTime() == auxiliarDate.getTime()) {
                                                nombretd = 'col' + h + 'ren' + y + 'tur' + z + 'emp' + arrValue[y];
                                                calendario += '<td class="calendario" onclick="tdclick(' + h + ',' + y + ',' + z + ',' + arrValue[y] + ');" id=' + nombretd + '>INCAPACIDAD</td>';
                                                contadorInc = 100;
                                                incidenciaFlag = 1;
                                            }

                                        }
                                    }
                                }
                                if (incidenciaFlag != 1) {
                                    for (contadorFest = 0; data[5].length > contadorFest; contadorFest++) {
                                        if (data[5][contadorFest].idEmp == arrValue[y]) {
                                            fechaComp = data[5][contadorFest].Date;
                                            arrDate = fechaComp.split('-');
                                            iniyear = arrDate[0];
                                            inimonth = arrDate[1] - 1;
                                            iniday = arrDate[2];
                                            comparacion = new Date(iniyear, inimonth, iniday).getTime();
                                            datocomparado = new Date(comparacion);
                                            if (datocomparado.getTime() == auxiliarDate.getTime()) {
                                                nombretd = 'col' + h + 'ren' + y + 'tur' + z + 'emp' + arrValue[y];
                                                calendario += '<td class="calendario" onclick="tdclick(' + h + ',' + y + ',' + z + ',' + arrValue[y] + ');" id=' + nombretd + '>DIA FESTIVO</td>';
                                                contadorFest = 100;
                                                incidenciaFlag = 1;
                                            }

                                        }
                                    }
                                }
                                if (incidenciaFlag != 1) {
                                    for (contadorFest = 0; data[4].length > contadorFest; contadorFest++) {
                                        if (data[4][contadorFest].idDept == arrDept[y]) {
                                            fechaComp = data[4][contadorFest].Date;
                                            arrDate = fechaComp.split('-');
                                            iniyear = arrDate[0];
                                            inimonth = arrDate[1] - 1;
                                            iniday = arrDate[2];
                                            comparacion = new Date(iniyear, inimonth, iniday).getTime();
                                            datocomparado = new Date(comparacion);
                                            if (datocomparado.getTime() == auxiliarDate.getTime()) {
                                                nombretd = 'col' + h + 'ren' + y + 'tur' + z + 'emp' + arrValue[y];
                                                calendario += '<td class="calendario" onclick="tdclick(' + h + ',' + y + ',' + z + ',' + arrValue[y] + ');" id=' + nombretd + '>DIA FESTIVO</td>';
                                                contadorFest = 100;
                                                incidenciaFlag = 1;
                                            }

                                        }
                                    }
                                }
                                if (incidenciaFlag != 1) {
                                    for (contadorFest = 0; data[3].length > contadorFest; contadorFest++) {
                                        if (data[3][contadorFest].idArea == 3) {
                                            fechaComp = data[3][contadorFest].Date;
                                            arrDate = fechaComp.split('-');
                                            iniyear = arrDate[0];
                                            inimonth = arrDate[1] - 1;
                                            iniday = arrDate[2];
                                            comparacion = new Date(iniyear, inimonth, iniday).getTime();
                                            datocomparado = new Date(comparacion);
                                            if (datocomparado.getTime() == auxiliarDate.getTime()) {
                                                nombretd = 'col' + h + 'ren' + y + 'tur' + z + 'emp' + arrValue[y];
                                                calendario += '<td class="calendario" onclick="tdclick(' + h + ',' + y + ',' + z + ',' + arrValue[y] + ');" id=' + nombretd + '>DIA FESTIVO</td>';
                                                contadorFest = 100;
                                                incidenciaFlag = 1;
                                            }

                                        }
                                    }
                                }
                                if (incidenciaFlag == 0) {
                                    var nombretd = 'col' + h + 'ren' + y + 'tur' + z + 'emp' + arrValue[y];
                                    calendario += '<td class="calendario" onclick="tdclick(' + h + ',' + y + ',' + z + ',' + arrValue[y] + ');" id=' + nombretd + '>' + arrText[y] + '</td>'
                                }
                                auxiliarDate.setDate(auxiliarDate.getDate() + 1);
                            }
                            calendario += '</tr>';
                            auxiliarDate.setDate(auxiliarDate.getDate() - diff - 1);
                        }
                    }


                }
                $('#calendario').append(calendario);
                $('#botonCale').attr('disabled', true)
                $('#botonImprimir').append('<button class="btn btn-warning" id="imprimir" name="imprimir" onclick=printDiv("turnonuevo","calendario")>Generar PDF</button>')
                vueApp.lEmployeesAssigment = Array.from(new Set(arrValue));
                vueApp.setlEmployeesWithOutAssigment();
            }

        });


    }
}

function diasemana(dia) {
    var dias = ["domingo", "lunes", "martes", "miercoles", "jueves", "viernes", "sabado"];
    var respuesta = dias[dia];
    return respuesta;
}

function meses(mes) {
    var meses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
    var respuesta = meses[mes];
    return respuesta;
}

Array.prototype.unique = function(a) {
    return function() { return this.filter(a) }
}(function(a, b, c) {
    return c.indexOf(a, b + 1) < 0
});