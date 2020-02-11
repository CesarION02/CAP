function printDiv(nombreDiv, calendario) {
    var contenido = document.getElementById(nombreDiv).innerHTML;
    var contenido2 = document.getElementById(calendario).innerHTML;
    var contenidoOriginal = document.body.innerHTML;

    document.body.innerHTML = contenido + contenido2;

    window.print();

    document.body.innerHTML = contenidoOriginal;
    $('#subpdf').append('<input type="file" id="pdf" name="pdf"><input class="btn btn-success" type="submit" value="Enviar"></form>');
}