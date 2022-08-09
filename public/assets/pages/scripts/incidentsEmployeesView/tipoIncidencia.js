function tipo_incidencia(opcion) {
    console.log("Actualizando datos", opcion.value);

    var stipos = document.getElementById("atipos").value;
    var atipos = stipos.split(",");

    var activar = 0;
    for (var i = 0; atipos.length > i; i++) {
        if (opcion.value == atipos[i]) {
            activar = 1;
        }
    }

    if (activar == 1) {
        document.getElementById("comentFrec").disabled = false;
        document.getElementById("comentarios").disabled = false;
        document.getElementById("comentarios").required = true;
        document.getElementById("sincomentarios").value = 1;
    } else {
        document.getElementById("comentFrec").disabled = true;
        document.getElementById("comentarios").disabled = true;
        document.getElementById("comentarios").required = false;
        document.getElementById("sincomentarios").value = 0;
    }
}