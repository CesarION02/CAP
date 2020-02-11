$(document).on('change', '#fechaini', function() {
    $("#fechafin").removeAttr("readonly");
});

$(document).on('change', '#fechafin', function() {
    var ini = new Date(document.getElementById("fechaini").value);
    var fin = new Date(document.getElementById("fechafin").value);

    if (ini.getTime() >= fin.getTime()) {
        swal("Error", "La fecha final no puede ser antes de la fecha inicial", "warning")
    } else {
        $("#nuevo").removeAttr("disabled");
    }

});