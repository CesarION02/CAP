function tdclick(col, ren, tur, emp) {
    console.log(col, ren, tur, emp);
    var columna = 'col' + col;
    var renglon = 'ren' + ren;
    var turno = 'tur' + tur;
    var empleado = 'emp' + emp;
    var columna1 = '';
    var renglon1 = '';
    var turno1 = '';
    var nombre1 = '';
    var empleado1 = '';
    var nombre = columna.concat(renglon);
    nombre = nombre.concat(turno);
    nombre = nombre.concat(empleado);
    var comparacion = document.getElementById(nombre).innerHTML;
    if (comparacion == 'Descanso') {
        if (col == 0) {
            col++;
        } else {
            col--;
        }
        columna1 = 'col' + col;
        renglon1 = 'ren' + ren;
        turno1 = 'tur' + tur;
        empleado1 = 'emp' + emp;
        nombre1 = columna1.concat(renglon1);
        nombre1 = nombre1.concat(turno1);
        nombre1 = nombre1.concat(empleado1);
        var regreso = document.getElementById(nombre1).innerHTML;
        document.getElementById(nombre).innerHTML = regreso;

    } else {
        document.getElementById(nombre).innerHTML = 'Descanso';
    }
}