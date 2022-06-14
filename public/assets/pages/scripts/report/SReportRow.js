class SReportRow {
    createRowBottom(tReport, numEmploye, hrs, delayMins, premMins, sundays, daysOff) {
        let row = [];
        if (tReport == oData.REP_HR_EX) {
            let daysOffTheorical = oData.lEmpWrkdDays[parseInt(numEmploye, 10)];
            row[0] = numEmploye;
            row[1] = "";
            row[2] = "";
            row[3] = "";
            row[4] = "";
            row[5] = "";
            row[6] = "";
            row[7] = "TOTAL =";
            row[8] = convertToHoursMins(hrs);
            row[9] = delayMins;
            row[10] = premMins;
            row[11] = sundays;
            row[12] = daysOff + 
                        " [" + (daysOffTheorical == undefined ? 0 : daysOffTheorical)  + "]";
            row[13] = "";
            row[14] = "";
        }
        else {        
            row[0] = numEmploye;
            row[1] = "";
            row[2] = "";
            row[3] = "TOTAL =";
            row[4] = hrs;
            row[5] = "";
        }

        return row;
    }

    convertStringHoursToMins(sHour) {
        let aHrs = sHour.split(':');
        
        return (aHrs[0] * 60) +  aHrs[1];
    }
}