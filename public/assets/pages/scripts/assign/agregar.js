function agregar() {
    var contador = document.getElementById("contador").value;
    var seleccion = document.getElementById("horario" + contador).value;
    contador++;
    var nameDiv = "div";
    nameDiv += contador;
    var div = '<div class="form-group ' + nameDiv + '"><label class="col-lg-3 control-label requerido">Horario:</label><div class="col-lg-4"><select name="horario' + contador + '" id="horario' + contador + '">'
    $.ajax({
        type: 'get',
        url: 'schedule_template',

        success: function(data) {
            for (var i = 0; data.length > i; i++) {
                if (data[i].id == seleccion) {
                    div += '<option selected value="' + data[i].id + '">' + data[i].name + '</option>';
                } else {
                    div += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                }
            }
            div += '</select></div><label class="col-lg-2 control-label requerido">Orden:</label><div class="col-lg-1"><input type="number" name="orden' + contador + '" id="orden' + contador + '" value="' + contador + '" style="width:70%"></div></div>'

            $('.box-body').append(div);
            $('#orden1').removeAttr('disabled');
            document.getElementById("contador").value = contador;
        }
    });


}