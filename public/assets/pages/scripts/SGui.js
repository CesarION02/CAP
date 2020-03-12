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
    var s = num + "";
    while (s.length < size) s = "0" + s;
    return s;
  }
}
