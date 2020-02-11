function UploadFile() {
    var blobFile = $('#pdf').prop("files")[0];
    var formImage = new FormData();
    formImage.append('userImage', blobFile);
    idimg = 'drop-area';
    uploadFormData(formImage);
}

function uploadFormData(formData) {
    $.ajax({
        url: "uploadimage",
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        success: function(data) {
            var cadena = '<img src="' + data + '"><input type="hidden" name="srcimagen' + idimg + '" id=srcimagen' + idimg + ' value="' + data + '">';
            $('#' + idimg).html(cadena);
        }
    });
}