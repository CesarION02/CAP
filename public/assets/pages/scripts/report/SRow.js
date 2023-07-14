class SRow {
    createRowBottom(tReport, numEmploye, hrJornada, mnJornada, hrTrabajado, mnTrabajado, hrAjustado, mnAjustado, hrTotal, mnTotal, delayMins, premMins, sundays, daysOff) {
        let row = [];
        var horas = 0;
        var minutos = 0;
        if (tReport == oData.REP_HR_EX) {
            let daysOffTheorical = oData.lEmpWrkdDays[parseInt(numEmploye, 10)];
            row[0] = numEmploye;
            row[1] = "";
            row[2] = "";
            row[3] = "";
            row[4] = "TOTAL:";
            if (mnJornada > 60) {
                horas = Math.floor(mnJornada / 60);
                minutos = Math.floor(mnJornada % 60);

                hrJornada = hrJornada + horas;
                mnJornada = minutos;
            }
            row[5] = (hrJornada > 9 ? hrJornada : "0" + hrJornada) + ":" + (mnJornada > 9 ? mnJornada : "0" + mnJornada);
            if (mnTrabajado > 60) {
                horas = Math.floor(mnTrabajado / 60);
                minutos = Math.floor(mnTrabajado % 60);

                hrTrabajado = hrTrabajado + horas;
                mnTrabajado = minutos;
            }
            row[6] = (hrTrabajado > 9 ? hrTrabajado : "0" + hrTrabajado) + ":" + (mnTrabajado > 9 ? mnTrabajado : "0" + mnTrabajado);
            if (mnAjustado > 60) {
                horas = Math.floor(mnAjustado / 60);
                minutos = Math.floor(mnAjustado % 60);

                hrAjustado = hrAjustado + horas;
                mnAjustado = minutos;
            }
            row[7] = (hrAjustado > 9 ? hrAjustado : "0" + hrAjustado) + ":" + (mnAjustado > 9 ? mnAjustado : "0" + mnAjustado);
            if (mnTotal > 60) {
                horas = Math.floor(mnTotal / 60);
                minutos = Math.floor(mnTotal % 60);

                hrTotal = hrTotal + horas;
                mnTotal = minutos;
            }
            row[8] = (hrTotal > 9 ? hrTotal : "0" + hrTotal) + ":" + (mnTotal > 9 ? mnTotal : "0" + mnTotal);
            row[9] = delayMins;
            row[10] = premMins;
            row[11] = sundays;
            row[12] = daysOff +
                " [" + (daysOffTheorical == undefined ? 0 : daysOffTheorical) + "]";
            row[13] = "";
            row[14] = "";
        } else {
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

        return (aHrs[0] * 60) + aHrs[1];
    }
}