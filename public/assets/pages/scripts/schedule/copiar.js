function copiar(num) {
    var horaAnteriorE = 0;
    var horaAnteriorS = 0;

    switch (num) {
        case 2:
            horaAnteriorE = document.getElementById("lunesE").value;
            horaAnteriorS = document.getElementById("lunesS").value;
            if (horaAnteriorE == "" || horaAnteriorS == "") {
                swal("Error", "El horario anterior no a sido asignado", "warning")
            } else {
                document.getElementById("martesE").value = horaAnteriorE;
                document.getElementById("martesS").value = horaAnteriorS;
            }
            break;
        case 3:
            horaAnteriorE = document.getElementById("martesE").value;
            horaAnteriorS = document.getElementById("martesS").value;
            if (horaAnteriorE == "" || horaAnteriorS == "") {
                swal("Error", "El horario anterior no a sido asignado", "warning")
            } else {
                document.getElementById("miercolesE").value = horaAnteriorE;
                document.getElementById("miercolesS").value = horaAnteriorS;
            }
            break;
        case 4:
            horaAnteriorE = document.getElementById("miercolesE").value;
            horaAnteriorS = document.getElementById("miercolesS").value;
            if (horaAnteriorE == "" || horaAnteriorS == "") {
                swal("Error", "El horario anterior no a sido asignado", "warning")
            } else {
                document.getElementById("juevesE").value = horaAnteriorE;
                document.getElementById("juevesS").value = horaAnteriorS;
            }
            break;
        case 5:
            horaAnteriorE = document.getElementById("juevesE").value;
            horaAnteriorS = document.getElementById("juevesS").value;
            if (horaAnteriorE == "" || horaAnteriorS == "") {
                swal("Error", "El horario anterior no a sido asignado", "warning")
            } else {
                document.getElementById("viernesE").value = horaAnteriorE;
                document.getElementById("viernesS").value = horaAnteriorS;
            }
            break;
        case 6:
            horaAnteriorE = document.getElementById("viernesE").value;
            horaAnteriorS = document.getElementById("viernesS").value;
            if (horaAnteriorE == "" || horaAnteriorS == "") {
                swal("Error", "El horario anterior no a sido asignado", "warning")
            } else {
                document.getElementById("sabadoE").value = horaAnteriorE;
                document.getElementById("sabadoS").value = horaAnteriorS;
            }
            break;
        case 7:
            horaAnteriorE = document.getElementById("sabadoE").value;
            horaAnteriorS = document.getElementById("sabadoS").value;
            if (horaAnteriorE == "" || horaAnteriorS == "") {
                swal("Error", "El horario anterior no a sido asignado", "warning")
            } else {
                document.getElementById("domingoE").value = horaAnteriorE;
                document.getElementById("domingoS").value = horaAnteriorS;
            }
            break;
    }

}