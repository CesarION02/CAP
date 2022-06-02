function eliminar() {
    var contador = document.getElementById("contador").value;

    if (contador == 1) {
        swal("Error", "No se puede borrar el primer puesto", "warning")
    } else {
        var nameDiv = ".div";
        nameDiv += contador;
        $(nameDiv).remove();
        if ((contador - 1) == 1) {
            $('#orden1').attr('disabled', 'disabled');
        }
        document.getElementById("contador").value = contador - 1;
    }
}