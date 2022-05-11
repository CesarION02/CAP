var app = new Vue({
    el: '#reportDelayApp',
    data: {
        vData: oData,
        vueGui: oGui,
        rowAdjusts: [],
        adjCategory: 0,
        inDateTime: "",
        outDateTime: "",
        isModifIn: false,
        isModifOut: false,
        adjType: 1,
        minsEnabled: false,
        overMins: 0,
        comments: "",
        vRow: null,
        adjTypeEnabled: true,
    },
    methods: {
        getCssClass(oRow, report) {
            if ((oRow.hasAbsence || !oRow.hasCheckOut || !oRow.hasCheckIn) && (oRow.events.length == 0)) {
                return 'absence';
            }
            if (oRow.hasSchedule == false && oRow.hasChecks == false) {
                return 'noprogramming';
            }
            if (oRow.isCheckSchedule || (oRow.events.length > 0 && oRow.hasChecks)) {
                return 'check';
            }
            if (report != oData.REP_DELAY) {
                if (oRow.entryDelayMinutes > 0) {
                    return 'delays';
                }
            }
            if (oRow.events.length > 0 && !oRow.hasChecks) {
                return 'ext-events';
            }
            if ((oRow.events.length > 0 || oRow.isDayOff > 0 || oRow.isHoliday > 0 || oRow.dayInhability > 0 || oRow.dayVacations > 0) &&
                (oRow.hasChecks || oRow.hasCheckOut)) {
                return 'events'
            }
            if (oRow.isIncompleteTeJourney) {
                return 'incomplete-te-journey';
            }
        },
        getDtCellCss(oRow, typeReg) {
            if (typeReg == 1 && oRow.isModifiedIn) {
                return 'dt-modified';
            }
            if (typeReg == 2 && oRow.isModifiedOut) {
                return 'dt-modified';
            }
        },
        newAdjust() {
            let applyTo = 0;
            let dtDate = "";
            let dtTime = "";

            oGui.showLoading(3000);

            switch (this.adjType) {
                case oData.ADJ_CONS.JE:
                case oData.ADJ_CONS.OR:
                case oData.ADJ_CONS.OF:
                    dtDate = this.vRow.inDate == null ? this.vRow.inDateTime : this.vRow.inDate;
                    dtTime = this.vRow.inDateTime.length > 10 ? this.vRow.inDateTime.substring(10) : "";
                    applyTo = 1;
                    break;

                case oData.ADJ_CONS.JS:
                case oData.ADJ_CONS.DHE:
                case oData.ADJ_CONS.AHE:
                case oData.ADJ_CONS.COM:
                    dtDate = this.vRow.outDate == null ? this.vRow.outDateTime : this.vRow.outDate;
                    dtTime = this.vRow.outDateTime.length > 10 ? this.vRow.outDateTime.substring(10) : "";
                    applyTo = 2;
                    break;

                default:
                    break;
            }

            if (!this.validate()) {
                return;
            }

            let route = '../prepayrolladjust';

            axios.post(route, {
                    adjust_type_id: this.adjType,
                    minutes: this.overMins,
                    apply_to: applyTo,
                    comments: this.comments,
                    dt_date: dtDate,
                    dt_time: dtTime,
                    employee_id: this.vRow.idEmployee
                })
                .then(res => {
                    console.log(res);
                    let oRes = res.data;

                    if (oRes.success) {
                        this.vRow.labelUpd = true;
                        this.setRowAdjusts();
                        oGui.showOk();
                    } else {
                        oGui.showError(oRes.msg);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                });
        },
        validate() {
            if (this.adjType == oData.ADJ_CONS.DHE || this.adjType == oData.ADJ_CONS.AHE) {
                if (!Number.isInteger(parseInt(this.overMins, 10))) {
                    oGui.showError("El valor de minutos debe ser entero");
                    return false;
                }
                if (parseInt(this.overMins, 10) <= 0) {
                    oGui.showError("El valor de minutos debe ser mayor a cero");
                    return false;
                }
            }

            if (this.adjType == oData.ADJ_CONS.DHE) {
                let minsToDiscount = 0;
                for (const elem of this.rowAdjusts) {
                    if (elem.adjust_type_id == oData.ADJ_CONS.DHE) {
                        minsToDiscount += elem.minutes;
                    }
                }

                if ((parseInt(this.overMins, 10) + minsToDiscount) > this.vRow.overWorkedMins) {
                    oGui.showError("No se puede descontar " + this.overMins + " min de tiempo extra. (Solo se puede descontar " +
                        "tiempo extra generado a partir de la hora de salida)");
                    return false;
                }
            }

            if (this.adjType == oData.ADJ_CONS.COM) {
                if (this.comments.length == 0) {
                    oGui.showError("Debe ingresar un comentario");
                    return false;
                }
            }

            return true;
        },
        deleteAdjust(oAdj) {
            oGui.showLoading(3000);

            let route = '../prepayrolladjust/' + oAdj.id;

            axios.delete(route)
                .then(res => {
                    console.log(res);
                    this.vRow.labelUpd = true;
                    this.setRowAdjusts();
                    oGui.showOk();
                })
                .catch(function(error) {
                    console.log(error);
                });
        },
        showModal(oRow) {
            this.vRow = oRow;
            this.setRowAdjusts();

            this.adjType = 1;
            this.minsEnabled = false;
            this.overMins = 0;
            this.comments = "";
            this.adjCategory = 0;
            this.inDateTime = "";
            this.outDateTime = "";
            this.isModifIn = false;
            this.isModifOut = false;

            let sIn = this.vRow.inDateTime.length == 10 ? this.vRow.inDateTime + " 00:00" : this.vRow.inDateTime.replace('   ', ' ');
            let sOut = this.vRow.outDateTime.length == 10 ? this.vRow.outDateTime + " 00:00" : this.vRow.outDateTime.replace('   ', ' ');

            let inD = moment(sIn);
            let outD = moment(sOut);

            this.inDateTime = inD.format('YYYY-MM-DDTHH:mm');
            this.outDateTime = outD.format('YYYY-MM-DDTHH:mm');

            this.onAdjustChange();

            $('#adjustsModal').modal('show');
        },
        setRowAdjusts() {
            let route = '../prepayrollrowadjusts';
            this.rowAdjusts = [];

            oGui.showLoading(3000);

            axios.get(route, {
                    params: {
                        start_date: this.vRow.inDateTime.substring(0, 10),
                        end_date: this.vRow.outDateTime.substring(0, 10),
                        employee_id: this.vRow.idEmployee
                    }
                })
                .then(res => {
                    this.rowAdjusts = res.data;
                })
                .catch(function(error) {
                    console.log(error);
                });
        },
        getAdjToRow(oRow) {
            let labels = "";
            for (const adj of this.vData.lAdjusts) {
                if (adj.employee_id == oRow.idEmployee) {
                    if (adj.apply_to == 1) {
                        let tiime = adj.dt_time != null ? (' ' + adj.dt_time) : '';
                        if ((adj.dt_date + tiime) == oRow.inDateTime) {
                            labels += adj.type_code + ' ';
                        }
                    } else {
                        let tiime = adj.dt_time != null ? (' ' + adj.dt_time) : '';
                        if ((adj.dt_date + tiime) == oRow.outDateTime) {
                            if (adj.adjust_type_id == oData.ADJ_CONS.COM) {
                                labels += adj.comments + ' ';
                            } else {
                                labels += adj.type_code + ' ';
                            }
                            if (adj.adjust_type_id == oData.ADJ_CONS.AHE ||
                                adj.adjust_type_id == oData.ADJ_CONS.DHE) {
                                labels += adj.minutes + 'min ';
                            }
                        }
                    }
                }
            }

            if (oRow.labelUpd || !(oRow.tempLabels === undefined)) {
                labels += "¡Pendiente de actualizar!";
            }

            if (oRow.isModifiedIn) {
                labels += "Entrada modificada. ";
            }

            if (oRow.isModifiedOut) {
                labels += "Salida modificada. ";
            }

            return labels;
        },
        onAdjustChange() {
            if (this.adjCategory == "2") {
                this.comments = "";
                this.adjType = oData.ADJ_CONS.COM;
                this.adjTypeEnabled = false;
            } else {
                this.adjType = oData.ADJ_CONS.JE;
                this.adjTypeEnabled = true;
                this.comments = "";
            }

            if (this.adjType == oData.ADJ_CONS.DHE || this.adjType == oData.ADJ_CONS.AHE) {
                this.minsEnabled = true;
            } else {
                this.minsEnabled = false;
            }
        },
        onTypeChange() {
            this.minsEnabled = this.adjType == oData.ADJ_CONS.DHE || this.adjType == oData.ADJ_CONS.AHE;
            this.overMins = 0;
        },
        adjustTimes() {
            oGui.showLoading(3000);

            let inDTime = this.inDateTime;
            let outDTime = this.outDateTime;

            axios.post(this.vData.registriesRoute, {
                    modif_in: this.isModifIn,
                    modif_out: this.isModifOut,
                    in_datetime: inDTime,
                    out_datetime: outDTime,
                    row: JSON.stringify(this.vRow)
                })
                .then(res => {
                    console.log(res);
                    this.vRow.tempLabels = "Entrada y/o salida modificadas.";
                    this.vData.lRows.push('refresh');
                    this.vData.lRows.pop();
                    oGui.showOk();
                })
                .catch(function(error) {
                    console.log(error);
                });
        }
    },
})