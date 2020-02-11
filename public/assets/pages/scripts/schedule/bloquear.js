$(document).on('change', '.check', function(e) {
    var desactivar = $(e.currentTarget).val();
    if (this.checked) {
        switch (desactivar) {
            case "1":
                $('#lunesE').val("");
                $('#lunesE').attr('disabled', 'disabled');
                $('#lunesS').val("");
                $('#lunesS').attr('disabled', 'disabled');
                break;
            case "2":
                $('#martesE').val("");
                $('#martesE').attr('disabled', 'disabled');
                $('#martesS').val("");
                $('#martesS').attr('disabled', 'disabled');
                break;
            case "3":
                $('#miercolesE').val("");
                $('#miercolesE').attr('disabled', 'disabled');
                $('#miercolesS').val("");
                $('#miercolesS').attr('disabled', 'disabled');
                break;
            case "4":
                $('#juevesE').val("");
                $('#juevesE').attr('disabled', 'disabled');
                $('#juevesS').val("");
                $('#juevesS').attr('disabled', 'disabled');
                break;
            case "5":
                $('#viernesE').val("");
                $('#viernesE').attr('disabled', 'disabled');
                $('#viernesS').val("");
                $('#viernesS').attr('disabled', 'disabled');
                break;
            case "6":
                $('#sabadoE').val("");
                $('#sabadoE').attr('disabled', 'disabled');
                $('#sabadoS').val("");
                $('#sabadoS').attr('disabled', 'disabled');
                break;
            case "7":
                $('#domingoE').val("");
                $('#domingoE').attr('disabled', 'disabled');
                $('#domingoS').val("");
                $('#domingoS').attr('disabled', 'disabled');
                break;
        }
    } else {
        switch (desactivar) {
            case "1":
                $('#lunesE').val("");
                $('#lunesE').removeAttr('disabled', 'disabled');
                $('#lunesS').val("");
                $('#lunesS').removeAttr('disabled', 'disabled');
                break;
            case "2":
                $('#martesE').val("");
                $('#martesE').removeAttr('disabled', 'disabled');
                $('#martesS').val("");
                $('#martesS').removeAttr('disabled', 'disabled');
                break;
            case "3":
                $('#miercolesE').val("");
                $('#miercolesE').removeAttr('disabled', 'disabled');
                $('#miercolesS').val("");
                $('#miercolesS').removeAttr('disabled', 'disabled');
                break;
            case "4":
                $('#juevesE').val("");
                $('#juevesE').removeAttr('disabled', 'disabled');
                $('#juevesS').val("");
                $('#juevesS').removeAttr('disabled', 'disabled');
                break;
            case "5":
                $('#viernesE').val("");
                $('#viernesE').removeAttr('disabled', 'disabled');
                $('#viernesS').val("");
                $('#viernesS').removeAttr('disabled', 'disabled');
                break;
            case "6":
                $('#sabadoE').val("");
                $('#sabadoE').removeAttr('disabled', 'disabled');
                $('#sabadoS').val("");
                $('#sabadoS').removeAttr('disabled', 'disabled');
                break;
            case "7":
                $('#domingoE').val("");
                $('#domingoE').removeAttr('disabled', 'disabled');
                $('#domingoS').val("");
                $('#domingoS').removeAttr('disabled', 'disabled');
                break;
        }
    }
});