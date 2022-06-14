function makeDocument() {
    let wb = XLSX.utils.book_new();
    wb.Props = {
        Title: "Reporte CAP",
        Subject: "Report",
        Author: "CAP",
        CreatedDate: new Date(2017, 12, 19)
    };

    wb.SheetNames.push("Test Sheet");
    let tableData = oTable.rows().data();
    let ws_data = [];

    let columnNames = [];
    let columns = oTable.settings()[0].aoColumns;
    for (const col of columns) {
        columnNames.push(col.sTitle);
    }

    if (oData.tReport == oData.REP_HR_EX) {
        columnNames.splice(3, 4);
        columnNames[2] = columnNames[2].replace('<span class="nobr">', '');
        columnNames[2] = columnNames[2].replace('</span>', '');
        columnNames.splice(4, 7);
    } else {
        columnNames.splice(5, 1);
    }

    ws_data.push(columnNames);

    // let oRowObj = new SReportRow();

    let numEmploye = tableData[0][0];
    let hrs = 0;
    let delayMins = 0;
    let premMins = 0;
    let sundays = 0;
    let daysOff = 0;
    tableData.each(function(rowValue, index) {
        let value = [...rowValue];

        if (value[0] != numEmploye) {
            let row = createRowBottom(oData.tReport, numEmploye, delayMins);

            ws_data.push(row);
            numEmploye = value[0];
            hrs = 0;
            delayMins = 0;
            premMins = 0;
            sundays = 0;
            daysOff = 0;
        }

        if (oData.tReport == oData.REP_HR_EX) {
            delayMins += (value[7] == "") ? 0 : parseInt(value[7], 10);
        } else {
            delayMins += (value[7] == "") ? 0 : parseInt(value[7], 10);
        }

        if (oData.tReport == oData.REP_HR_EX) {
            value.splice(3, 4);
            value.splice(4, 7);
        } else {
            value.splice(5, 1);
        }

        ws_data.push(value);
    });

    let row = createRowBottom(oData.tReport, numEmploye, delayMins);

    ws_data.push(row);

    let ws = XLSX.utils.aoa_to_sheet(ws_data);
    wb.Sheets["Test Sheet"] = ws;
    return XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
}

function s2ab(s) {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);

    for (var i = 0; i < s.length; i++) {
        view[i] = s.charCodeAt(i) & 0xFF;
    }

    return buf;
}

$("#button-a").click(function() {
    let wbout = makeDocument();
    let blob = new Blob([s2ab(wbout)], { type: "application/octet-stream" });
    saveAs(blob, oData.tReport == oData.REP_HR_EX ? "PV_Reporte_CAP.xlsx" : "RET_Reporte_CAP.xlsx");
});

function createRowBottom(tReport, numEmploye, hrs) {
    let row = [];
    row[0] = numEmploye;
    row[1] = "";
    row[2] = "TOTAL =";
    row[3] = hrs;

    return row;
}