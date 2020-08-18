$(document).on('change', '#semana', function() {
    var semana = document.getElementById("semana").value;

    $.ajax({
        type: 'get',
        url: 'recoverPDF',
        data: { 'semana': semana },

        success: function(data) {
            var idWeek = data.week_id;
            var name = data.url;
            var cadenaPDF = '<iframe width="800" height="600" src="http://192.168.1.233:8080/cap/storage/app/public/' + name + '" frameborder="0"></iframe>';

            //$("#Antigua").empty(" ");
            $("#mostrarPdf").empty("");
            $("#mostrarPdf").append(cadenaPDF);
            document.getElementById("pastWeek").value = idWeek;
            $('#copiar').attr('disabled', false)
            $('#rotar').attr('disabled', false)


        },
        error: function() {
            console.log('falle');
        }
    });
});
$(document).on('change', '#anio', function() {
    var anio = document.getElementById("anio").value;

    $.ajax({
        type: 'get',
        url: 'recoverWeek',
        data: { 'anio': anio },

        success: function(data) {
            var aux = '<select id="semana" name="semana"><option value="0">Selecciona semana</option>'
            for (var i = 0; data.length > i; i++) {
                aux += '<option value="' + data[i].id + '">' + data[i].start + '-' + data[i].end + '</option>'
            }
            aux += '</select>'

            $("#selectsemana").empty("");
            $("#selectsemana").append(aux);

        },
        error: function() {
            console.log('falle');
        }
    });
});