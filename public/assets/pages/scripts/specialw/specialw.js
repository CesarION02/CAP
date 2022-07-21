function addComment() {
    var combo = document.getElementById("comentFrec");
    var selected = combo.options[combo.selectedIndex].text;

    var texto = document.getElementById("comentarios").value;
    if (texto == '') {
        texto = selected;
    } else {
        texto = texto + " ," + selected;
    }
    //document.getElementById("comentarios").text(texto);
    $('#comentarios').append(texto);
}