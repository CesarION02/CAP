function agregar() {
    var contador = document.getElementById("contador").value;
    contador++;
    var nameDiv = "div";
    nameDiv += contador;
    var div = '<div class="form-group ' + nameDiv + '"><label class="col-lg-3 control-label">Plantilla</label><div class="col-lg-4"><select name="horario' + contador + '" id="horario' + contador + '">'
    $.ajax({
        type: 'get',
        url: 'schedule_template',

        success: function(data) {
            for (var i = 0; data.length > i; i++) {
                div += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
            }
            div += '</select></div><label class="col-lg-1 control-label">Orden:</label><div class="col-lg-1"><input type="number" name="orden' + contador + '" id="orden' + contador + '" style="width:70%"></div></div>'

            $('.box-body').append(div);
            $('#orden1').removeAttr('disabled');
            document.getElementById("contador").value = contador;
        }
    });


}