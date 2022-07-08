$(document).on('change', '.departamento', function(e) {
    var departamento = document.getElementById("department_id").value;

    $.ajax({
        type: 'get',
        url: rutaPuesto,
        data: { 'departamento': departamento },

        success: function(data) {
            $('#job').empty("");
            if (data[1] != 0) {
                listaSelect = '<select id="job_id" name="job_id" class="form-control">';
                for (var i = 0; data[0].length > i; i++) {
                    listaSelect += '<option value="' + data[0][i].idJob + '">' + data[0][i].nameJob + '</option>'
                }
                listaSelect += '</select>'
                $('#job').append(listaSelect);
            }
        },
        error: function() {
            console.log('falle');
        }
    });
});

$(document).ready(function() {
    var departamento = document.getElementById("department_id").value;

    $.ajax({
        type: 'get',
        url: rutaPuesto,
        data: { 'departamento': departamento },

        success: function(data) {
            $('#job').empty("");
            if (data[1] != 0) {
                listaSelect = '<select id="job_id" name="job_id" class="form-control">';
                for (var i = 0; data[0].length > i; i++) {
                    listaSelect += '<option value="' + data[0][i].idJob + '">' + data[0][i].nameJob + '</option>'
                }
                listaSelect += '</select>'
                $('#job').append(listaSelect);
            }
        },
        error: function() {
            console.log('falle');
        }
    });
});