$(document).on('change', '#end_date', function() {
    var ini = moment(document.getElementById("start_date").value);
    var fin = moment(document.getElementById("end_date").value);
    var comparacion = document.getElementById("start_date").value;
    if (comparacion != "") {
        if (ini.getTime() > fin.getTime()) {
            swal("Error", "La fecha final no puede ser antes de la fecha inicial", "warning")
            document.getElementById('end_date').value = ini;
        }
    } else {
        swal("Error", "La fecha inicial no puede ser vacía", "warning")
        document.getElementById('end_date').value = ini;
    }
    if (comparacion != "") {
        var diff = fin.getTime() - ini.getTime();
        diff = Math.round(diff / (1000 * 60 * 60 * 24));
        for (var i = 0; diff + 1 >= i; i++) {
            if (i == 0) {
                ini.setDate(ini.getDate() + 0);
                alert(ini);
            } else {
                ini.setDate(ini.getDate() + 1);
                alert(ini);
            }

        }
    } else {

    }
});