$(document).on('change', '.tipo', function(e) {
    var tipo = $(e.currentTarget).val();
    if (tipo == 0) {
        $('#areas').prop('disabled', true).trigger("chosen:updated");
        $('#employees').prop('disabled', true).trigger("chosen:updated");
    } else if (tipo == 1) {
        $('#areas').prop('disabled', false).trigger("chosen:updated");
        $('#employees').prop('disabled', true).trigger("chosen:updated");

    } else if (tipo == 2) {
        $('#areas').prop('disabled', true).trigger("chosen:updated");
        $('#employees').prop('disabled', false).trigger("chosen:updated");
    }
});