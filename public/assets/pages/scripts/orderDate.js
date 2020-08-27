function order(date) {
    var auxDate = date.split("-");

    var newDate = "";
    newDate = newDate.concat(auxDate[2], "/", auxDate[1], "/", auxDate[0]);

    return newDate;
}