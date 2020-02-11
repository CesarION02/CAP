$(document).on('change', '#semana', function() {
    var semana = document.getElementById("semana").value;

    $.ajax({
        type: 'get',
        url: 'recoverPDF',
        data: { 'semana': semana },

        success: function(data) {
            var idWeek = data.week_id;
            var name = data.url;
            var cadenaPDF = '<iframe width="800" height="600" src="http://localhost/csv/checador/storage/app/public/' + name + '" frameborder="0"></iframe>';

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