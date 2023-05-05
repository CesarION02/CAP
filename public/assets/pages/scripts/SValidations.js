class SValidations {
    validateSchedule(startDate, endDate, idEmployee, routeValidation, formName, schedule, idRef) {
        oGui.showLoading(3000);
    
        // realiza petición GET con axios
        axios.get(routeValidation, {
            params: {
                start_date: startDate,
                end_date: endDate,
                id_employee: idEmployee,
                schedule: schedule,
                id_ref: idRef
            }
        })
        .then(res => {
            if (res.status == 200) {
                let message = res.data;
                if (message.length > 0) {
                    swal({
                        title: "¿Desea continuar?",
                        text: message,
                        icon: "warning",
                        dangerMode: true,
                        buttons: ["Cancelar", "Sí, continuar"],
                    })
                    .then((willContinue) => {
                        if (willContinue) {
                            document.querySelector('#' + formName).submit();
                        }
                        else {
                        swal("Se canceló la operación");
                        }
                    });
                }
                else {
                    document.querySelector('#' + formName).submit();
                }
            }
            else {
                oGui.showError("Ocurrió un error al validar el horario");
                document.querySelector('#' + formName).submit();
            }
        })
        .catch(function(error) {
            console.log(error);
        });
    }
}