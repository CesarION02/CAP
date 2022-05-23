class SGui {
    showLoading(dTime) {
        swal("Espere...", {
            buttons: false,
            timer: dTime,
        });
    }

    showOk() {
        swal("Realizado", "Proceso completado con éxito", "success");
    }

    showError(sMessage) {
        swal("¡Error!", sMessage, "error");
    }

    showMessage(title, sMessage, icon) {
        swal(title, sMessage, icon);
    }

    confirm(title, sMessage, icon, danger = false) {
        return swal({
            title: title,
            text: sMessage,
            icon: icon,
            buttons: ["Cancelar", "Aceptar"],
            dangerMode: danger,
        })
    }

    showConfirm(title, sMessage, icon, danger = false){
        swal({
                title: title,
                text: sMessage,
                icon: icon,
                buttons: true,
                dangerMode: danger,
            })
            .then((value) => {
                if (value) {
                    return true;
                } else {
                    return false;
                }
            });
    }

    pad(num, size) {

        if (num.length == 0) {
            return "";
        }

        let s = num + "";

        while (s.length < size) s = "0" + s;
        return s;
    }

    formatDate(date) {
        let dt = moment(date).format('DD/MM/YYYY');
        return dt;
    }

    formatDateTime(dateTime) {
        let dt = moment(dateTime.replace('   ', ' ')).format('DD/MM/YYYY HH:mm:ss');
        return dt;
    }

    /**
     * Convierte minutos decimales a horas en formato HH:mm
     * 
     * @param {integer} mins 
     */
    formatMinsToHHmm(mins) {
        if (mins == 0 || mins == null || mins == undefined) {
            return "00:00";
        }

        let isNegative = false;
        if (mins < 0) {
            isNegative = true;
            mins = Math.abs(mins);
        }

        let hours = Math.floor(mins / 60);
        let minutes = mins % 60;

        hours = hours < 10 ? ('0' + hours) : hours;
        minutes = minutes < 10 ? ('0' + minutes) : minutes;

        return (isNegative ? "-" : "") + hours + ":" + minutes;
    }
}