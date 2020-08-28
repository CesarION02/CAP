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
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [day, month, year].join('/');
}
}
