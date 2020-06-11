class SReportRow {
    createRowBottom(tReport, numEmploye, hrs, delayMins, premMins, sundays, daysOff) {
        let row = [];
        if (tReport == oData.REP_HR_EX) {
            row[0] = numEmploye;
            row[1] = "";
            row[2] = "";
            row[3] = "TOTAL =";
            row[4] = convertToHoursMins(hrs);
            row[5] = delayMins;
            row[6] = premMins;
            row[7] = sundays;
            row[8] = daysOff;
            row[9] = "";
            row[10] = "";
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