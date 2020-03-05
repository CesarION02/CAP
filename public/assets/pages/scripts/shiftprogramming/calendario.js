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

            success: function(data) {
                calendario += '</tr><tr>';
                var turno = data[0].idWork;
                for (var z = 0; todosTurnos.length > z; z++) {
                    for (var x = 0; data.length > x; x++) {
                        if (todosTurnos[z] == data[x].idWork) {
                            nombreTurno = data[x].nameWork;
                            entrada = data[x].entry;
                            salida = data[x].departure;
                        }
                    }
                    calendario += '<th COLSPAN="' + (diff + 1) + '">' + nombreTurno + ' ' + entrada + ' a ' + salida + '</th></tr>'
                    for (var y = 0; arrTurno.length > y; y++) {
                        if (todosTurnos[z] == arrTurno[y]) {
                            calendario += '<tr>'

                            for (var h = 0; diff >= h; h++) {
                                var nombretd = 'col' + h + 'ren' + y + 'tur' + z + 'emp' + arrValue[y];
                                calendario += '<td class="calendario" onclick="tdclick(' + h + ',' + y + ',' + z + ',' + arrValue[y] + ');" id=' + nombretd + '>' + arrText[y] + '</td>'
                            }
                            calendario += '</tr>';
                        }
                    }


                }
                $('#calendario').append(calendario);
                $('#botonCale').attr('disabled', true)
                $('#botonImprimir').append('<button class="btn btn-warning" id="imprimir" name="imprimir" onclick=printDiv("turnonuevo","calendario")>Generar PDF</button>')
            }

        });



    }
}

function diasemana(dia) {
    var dias = ["lunes", "martes", "miercoles", "jueves", "viernes", "sabado", "domingo"];
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