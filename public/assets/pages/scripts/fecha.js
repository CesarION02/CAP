$(document).on('change', '#end_date', function() {
    var ini = new Date(document.getElementById("start_date").value);
    var fin = new Date(document.getElementById("end_date").value);
    var comparacion = document.getElementById("start_date").value;
    if (comparacion != "") {
        if (ini.getTime() > fin.getTime()) {
            swal("Error", "La fecha final no puede ser antes de la fecha inicial", "warning")
            document.getElementById('end_date').value = ini;
        }
    } else {
        swal("Error", "La fecha inicial no puede ser vacia", "warning")
        document.getElementById('end_date').value = ini;
    }
});