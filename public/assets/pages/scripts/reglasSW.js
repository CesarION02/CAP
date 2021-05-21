$(document).on('change', '#dates', function() {
    var ini = new Date(document.getElementById("datei").value);
    var fin = new Date(document.getElementById("dates").value);
    var comparacion = document.getElementById("datei").value;
    if (comparacion != "") {
        if (ini.getTime() > fin.getTime()) {
            swal("Error", "La fecha final no puede ser antes de la fecha inicial", "warning")
            document.getElementById('dates').value = ini;
        } else {
            var diff = fin - ini;
            // Calcular días
            diferenciaDias = Math.floor(diff / (1000 * 60 * 60 * 24));
            if (diferenciaDias > 5) {
                swal("Error", "Cambios de turno deben ser menores a una semana", "warning");
                document.getElementById('end_date').value = "";
            }
        }
    } else {
        swal("Error", "La fecha inicial no puede ser vacía", "warning")
        document.getElementById('dates').value = ini;
    }
});