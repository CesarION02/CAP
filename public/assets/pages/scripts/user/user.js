function contrasena() {
    if ($('#contrasenia').prop('checked')) {

        $('#password').attr('disabled', false);
        $('#passwordnu').attr('disabled', false);

        document.getElementById('con').value = 1;

    } else {

        $('#password').attr('disabled', true);
        $('#passwordnu').attr('disabled', true);

        document.getElementById('con').value = 0;

    }
}