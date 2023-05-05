function addComment() {
    var combo = document.getElementById("comentFrec");
    var selected = combo.options[combo.selectedIndex].text;

    var texto = document.getElementById("comentarios").value;
    if (texto == '') {
        texto = selected;
    } else {
        texto = texto + " ," + selected;
    }
    //document.getElementById("comentarios").text(texto);
    $('#comentarios').append(texto);
}

const form = document.querySelector('#specialWFormId');

form.addEventListener('submit', function (e) {
    // prevent the form from submitting
    e.preventDefault();

    // get the values submitted in the form
    const startDate = document.querySelector('#datei').value;
    const endDate = document.querySelector('#dates').value;
    const idEmployee = document.querySelector('#employee_id').value;

    // Validación de las fechas de inicio y fin y que el id del empleado sea mayor que 0
    if (startDate == "") {
        oGui.showError("Debe seleccionar una fecha de inicio");
        return;
    }
    if (endDate == "") {
        oGui.showError("Debe seleccionar una fecha de fin");
        return;
    }
    if (idEmployee == 0) {
        oGui.showError("Debe seleccionar un empleado");
        return;
    }

    let oValidation = new SValidations();

    oValidation.validateSchedule(startDate, endDate, idEmployee, routeValSch, "specialWFormId", "specialW", idSpecialW);
});