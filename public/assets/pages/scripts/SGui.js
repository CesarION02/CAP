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
    let dt = moment(dateTime).format('DD/MM/YYYY HH:mm:ss');
    return dt;
  }

  /**
   * Convierte minutos decimales a horas en formato HH:mm
   * 
   * @param {integer} mins 
   */
  formatMinsToHHmm(mins) {
    let hours = Math.floor(mins / 60);  
    let minutes = mins % 60;

    hours = hours < 10 ? ('0' + hours) : hours;
    minutes = minutes < 10 ? ('0' + minutes) : minutes;

    return hours + ":" + minutes;
  }
}
