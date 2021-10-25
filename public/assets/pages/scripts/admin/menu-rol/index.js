$('.menu_rol').on('change', function() {
    var data = {
        menu_id: $(this).data('menuid'),
        rol_id: $(this).val(),
        _token: $('input[name=_token]').val()
    };
    if ($(this).is(':checked')) {
        data.estado = 1
    } else {
        data.estado = 0
    }
    $.ajax({
        url: '../admin/menu-rol',
        type: 'POST',
        dataType: 'JSON',
        data: data,
        success: function(respuesta) {

        }
    });
});