function agregar() {
    var contador = document.getElementById("contador").value;
    contador++;
    var nameDiv = "div";
    nameDiv += contador;
    var div = '<div class="form-group ' + nameDiv + '"><label class="col-lg-3 control-label requerido">Nombre puesto:</label><div class="col-lg-8"><input type="text" class="form-control" required name="puesto' + contador + '" id="puesto' + contador + '"></div><div class="col-lg-1"><button type="button" class="btn btn-primary" onclick="eliminar()"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></div></div>'
    $('.box-body').append(div);
    document.getElementById("contador").value = contador;

}