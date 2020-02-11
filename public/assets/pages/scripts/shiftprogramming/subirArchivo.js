function UploadFile() {
    var blobFile = $('#pdf').prop("files")[0];
    var formImage = new FormData();
    formImage.append('userImage', blobFile);

    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'subirArchivo',
        data: { 'formImage': formImage },

        success: function(data) {

        },
    });
}