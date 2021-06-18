var app = new Vue({
    el: '#reportDelayApp',
    data: {
        vData: oData,
        vueGui: oGui,
        rowAdjusts: [],
        adjType: 1,
        minsEnabled: false,
        overMins: 0,
        comments: "",
        vRow: null
    },
    methods: {
        getCssClass(oRow, report) {
            if (oRow.hasAbsence || !oRow.hasCheckOut || !oRow.hasCheckIn) {
                return 'absence';
            }
            if (oRow.hasschedule == false) {
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
                    this.vRow.labelUpd = true;
                    this.setRowAdjusts();
                    oGui.showOk();
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
                            labels += adj.type_code + ' ';
                            if (adj.adjust_type_id == oData.ADJ_CONS.AHE ||
                                adj.adjust_type_id == oData.ADJ_CONS.DHE) {
                                labels += adj.minutes + 'min ';
                            }
                        }
                    }
                }
            }

            if (oRow.labelUpd) {
                labels += '¡Pendiente de actualizar!';
            }

            return labels;
        },
        onTypeChange() {
            this.minsEnabled = this.adjType == oData.ADJ_CONS.DHE || this.adjType == oData.ADJ_CONS.AHE;
            this.overMins = 0;
        }
    },
})