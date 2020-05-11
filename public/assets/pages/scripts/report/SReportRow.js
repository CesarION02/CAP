class SReportRow {
    createRowBottom(tReport, numEmploye, hrs, sundays, daysOff) {
        let row = [];
        if (tReport == oData.REP_HR_EX) {
            row[0] = numEmploye;
            row[1] = "";
            row[2] = "";
            row[3] = "TOTAL =";
            row[4] = convertToHoursMins(hrs);
            row[5] = sundays;
            row[6] = daysOff;
            row[7] = "";
            row[8] = "";
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