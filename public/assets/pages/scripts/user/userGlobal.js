function llenar() {
    var select = document.getElementById("colaborador"); // Obtener el elemento select por su ID
    var valorSeleccionado = select.value;

    if(valorSeleccionado == "0"){
        swal("Error", "Se tiene que seleccionar a un colaborador", "warning");
    }else{
        var json = document.getElementById("global").value;
        json = JSON.parse(json);
        
        var idABuscar = parseInt(valorSeleccionado);
        var registroEncontrado = json.find(function(json) {
            return json.id_global_user === idABuscar;
        });

        if( typeof registroEncontrado !== 'undfined'){
            document.getElementById('name').value = registroEncontrado.username;
            document.getElementById('fname').value = registroEncontrado.username;
            document.getElementById('email').value = registroEncontrado.email;
            document.getElementById('email').removeAttribute('disabled');
            document.getElementById('employee_id').removeAttribute('disabled');
            document.getElementById('fpassword').value = registroEncontrado.password;
            document.getElementById('fglobal').value = registroEncontrado.id_global_user;

            var selectElement = document.getElementById('employee_id');

            // Asignar el valor deseado al select
            selectElement.value = registroEncontrado.employee_num;

            // Establecer la opción como seleccionada
            selectElement.options[selectElement.selectedIndex].setAttribute('selected', 'selected');
            document.getElementById('employee_id').setAttribute('disabled', 'disabled');
            document.getElementById('femployee_id').value = registroEncontrado.employee_num;
            document.getElementById('guardar').removeAttribute('disabled');
            document.getElementById('deshacer').removeAttribute('disabled');
            document.getElementById("colaborador").setAttribute('disabled', 'disabled');
            document.getElementById("seleccionar").setAttribute('disabled', 'disabled');
        }else{
            swal("Error", "Se tiene que seleccionar a un colaborador", "warning");
        }
    }
}
