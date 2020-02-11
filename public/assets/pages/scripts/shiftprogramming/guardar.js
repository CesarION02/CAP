function guardar() {
    var ini = document.getElementById("fechaini").value;
    var fin = document.getElementById("fechafin").value;
    var weekFlag = document.getElementById("weekFlag").value;
    var departFlag = document.getElementById("departFlag").value;
    var pdfFlag = document.getElementById("pdfFlag").value;
    var semana = new Date(ini).getWeekNumber();
    var departamento = [];
    var cerrado = [];
    var turno = [];
    var cadenaturno = "turno";
    var cadenacerrado = "#cerrar";
    var numDep = 0;
    var nombre = [];
    var cont = 0;
    var cont1 = 0;
    var regex = /(\d+)/g;
    var turnoflag = 0;
    var nombreEmpleado = [];
    var Empleado = [];
    var arrTurno = [];
    var arrJob = [];
    var arrDept = [];
    var arrCalendarioEmpleados = [];
    $('.turno').each(function() {
        nombre[cont] = this.name;
        numDep = this.name.match(regex);
        departamento[cont] = numDep[0];
        cadenaturno = cadenaturno.concat(departamento[cont]);
        cadenacerrado = cadenacerrado.concat(departamento[cont]);
        turno[cont] = document.getElementById(cadenaturno).value;
        if ($(cadenacerrado).prop('checked')) {
            cerrado[cont] = 2;
        } else {
            cerrado[cont] = 1;
        }
        if (turno[cont] == 0 && cerrado[cont] == 1) {
            turnoflag = 1;

        } else if (turno[cont] == 0 && cerrado[cont] == 2) {
            turno[cont] = 1;
        }
        cont++;
        cadenaturno = "turno";
        cadenacerrado = "#cerrar";
    });
    var comparacion = 0;
    var contador = 0;
    var dias = 0;
    $('.calendario').each(function() {

        var cadenatabla = this.id;
        var auxiliar2 = cadenatabla.split('emp');
        if (comparacion == auxiliar2[1]) {
            dias++;
        } else {
            arrCalendarioEmpleados[contador] = auxiliar2[1];
            comparacion = auxiliar2[1];
            contador++;
            dias = 1;
        }

    });
    comparacion = 0;
    if (contador > 0) {
        var arrCalendarioDias = new Array(contador - 1);
        var contX = 0;
        var contY = 0;
        $('.calendario').each(function() {
            cadenatabla = this.id;
            auxiliar2 = cadenatabla.split('emp');
            if (comparacion == auxiliar2[1]) {
                contX++;
                if (document.getElementById(cadenatabla).innerHTML == 'Descanso') {
                    arrCalendarioDias[contY - 1][contX] = 1;
                } else {
                    arrCalendarioDias[contY - 1][contX] = 0;
                }
            } else {
                comparacion = auxiliar2[1];
                contY++;
                contX = 0;
                arrCalendarioDias[contY - 1] = new Array(dias);

                if (document.getElementById(cadenatabla).innerHTML == 'Descanso') {
                    arrCalendarioDias[contY - 1][contX] = 1;
                } else {
                    arrCalendarioDias[contY - 1][contX] = 0;
                }

            }
        });
    }

    cont = 0;
    $('.sel').each(function() {
        if (Empleado[cont] = $(this).val() != 0) {
            Empleado[cont] = $(this).val();
            nombreEmpleado[cont] = this.name;
            auxiliar = nombreEmpleado[cont].split(',');
            arrDept[cont] = auxiliar[1].substring(1);
            arrTurno[cont] = auxiliar[2].substring(1);
            arrJob[cont] = auxiliar[4].substring(1);
            cont++;
        }
    });
    $.ajax({
        type: 'post',
        url: 'guardar',
        data: { 'ini': ini, 'fin': fin, 'semana': semana, 'departamento': departamento, 'turno': turno, 'cerrado': cerrado, 'turnoflag': turnoflag, 'nombreEmpleado': nombreEmpleado, 'Empleado': Empleado, 'arrDept': arrDept, 'arrTurno': arrTurno, 'arrJob': arrJob, 'arrCalendarioEmpleados': arrCalendarioEmpleados, 'arrCalendarioDias': arrCalendarioDias, 'weekFlag': weekFlag, 'departFlag': departFlag, 'pdfFlag': pdfFlag },

        success: function(data) {
            swal("Guadado", "La programacion se guardo con exito", "sucess");
            document.getElementById("weekFlag").value = data[0];
            document.getElementById("departFlag").value = 1;
            if (data[1] != 0) {
                var pdf = '<a target="_blank" href=http://localhost/csv/checador/storage/app/public/' + data[1] + '>IMPRIMIR PDF';
                $('#pdf').append(pdf);
                document.getElementById("pdfFlag").value = data[0];
            }
        },
    });
}

Date.prototype.getWeekNumber = function() {
    var d = new Date(+this); //Creamos un nuevo Date con la fecha de "this".
    d.setHours(0, 0, 0, 0); //Nos aseguramos de limpiar la hora.
    d.setDate(d.getDate() + 4 - (d.getDay() || 7)); // Recorremos los días para asegurarnos de estar "dentro de la semana"
    //Finalmente, calculamos redondeando y ajustando por la naturaleza de los números en JS:
    return Math.ceil((((d - new Date(d.getFullYear(), 0, 1)) / 8.64e7) + 1) / 7);
};