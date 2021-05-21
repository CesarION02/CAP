$(document).on('change', '#end_date', function() {
    var ini = new Date(document.getElementById("start_date").value);
    var fin = new Date(document.getElementById("end_date").value);
    var comparacion = document.getElementById("start_date").value;
    if (comparacion != "") {
        if (ini.getTime() > fin.getTime()) {
            swal("Error", "La fecha final no puede ser antes de la fecha inicial", "warning")
            document.getElementById('end_date').value = ini;
        } else {
            var diff = fin - ini;
            // Calcular días
            diferenciaDias = Math.floor(diff / (1000 * 60 * 60 * 24));
            if (diferenciaDias < 6) {
                swal("Error", "Asignación de horario deben ser de al menos una semana", "warning");
                document.getElementById('end_date').value = "";
            }
        }
    } else {
        swal("Error", "La fecha inicial no puede ser vacía", "warning")
        document.getElementById('end_date').value = ini;
    }
});