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
        selComment: "",
        vRow: null,
        adjTypeEnabled: true,
        lCommentsAdjsTypes: [],
        lComments: [],
        haveComments: false,
        resumeComments: [],
        nameEmployee: "",
        indexRow: null,
        isAdjustsDisabled: false,
        isDisabledByComments: false,
        startDate: oData.startDate,
        endDate: oData.endDate,
        dateInit: null,
        dateEnd: null,
        dataSchedules: [],
        lUsers: oData.lUsers,
    },
    mounted() {
        this.haveComments = Object.keys(this.vData.lCommentsAdjsTypes).length > 0 ? Object.keys(this.vData.lCommentsAdjsTypes[this.adjType.toString()]).length > 0 : false;
        this.lComments = Object.keys(this.vData.lCommentsAdjsTypes).length > 0 ? this.vData.lCommentsAdjsTypes[this.adjType.toString()] : [];
        let self = this;
        $('#comentFrec').on('select2:select', function(e) {
            self.selComment = e.params.data.text;
        });

        var horarios = [];
        for (var i = 0; i < self.vData.lRows.length; i++) {
            horarios.push(self.vData.lRows[i].scheduleText);
        }

        let unique = [...new Set(horarios)];
        self.dataSchedules.push({ id: 'NA', text: 'No aplica' });
        for (let i = 0; i < unique.length; i++) {
            self.dataSchedules.push({ id: unique[i], text: unique[i] });
        }
    },
    methods: {
        getCssClass(oRow, report) {
            if ((oRow.hasAbsence || !oRow.hasCheckOut || !oRow.hasCheckIn) && (oRow.events.length == 0) && oRow.workable && !oRow.isHoliday) {
                return 'absence';
            }
            if (oRow.hasSchedule == false && oRow.hasChecks == false) {
                return 'noprogramming';
            }
            if (oRow.isCheckSchedule || (oRow.events.length > 0 && oRow.hasChecks) || oRow.isDayRepeated) {
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
            if (oRow.isHoliday > 0 && oRow.hasChecks) {
                return 'events'
            }
            if ((oRow.events.length > 0 || oRow.isDayOff > 0 || oRow.dayInhability > 0 || oRow.dayVacations > 0) &&
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

            if (this.adjCategory == 3) {
                if (moment(this.dateInit) > moment(this.dateEnd)) {
                    oGui.showMessage('', 'La fecha final debe ser mayor a la fecha inicial', 'error');
                    return;
                }
                if (this.dateInit == null || this.dateEnd == null) {
                    oGui.showMessage('', 'Debe seleccionar un rango de fecha', 'error');
                    return;
                }
            }

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
                    employee_id: this.vRow.idEmployee,
                    dateInit: this.dateInit,
                    dateEnd: this.dateEnd,
                    adjCategory: this.adjCategory,
                })
                .then(res => {
                    let oRes = res.data;

                    if (oRes.success) {
                        if (oRes.is_range) {
                            this.updateAdjRows(this.vRow.idEmployee, oRes.data);
                        } else {
                            this.vRow.labelUpd = true;
                            this.vRow.adjusts.push(res.data.data);
                            // this.setRowAdjusts();
                            this.rowAdjusts = this.vRow.adjusts;
                        }
                        this.comments = "";
                        oGui.showOk();
                    } else {
                        oGui.showError(oRes.msg);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                });
        },

        updateAdjRows(employee_id, data) {
            var arr_index = [];
            this.vData.lRows.findIndex((element, index) => {
                if (element.idEmployee == employee_id) {
                    arr_index.push(index);
                }
            })
            for (let adj of data) {
                if (adj.apply_to == 1) {
                    for (let i = 0; i < arr_index.length; i++) {
                        var dt = moment(this.vData.lRows[arr_index[i]].inDateTime).format('YYYY-MM-DD');
                        if (dt == adj.dt_date) {
                            this.vData.lRows[arr_index[i]].adjusts.push(adj);
                            this.vData.lRows[arr_index[i]].labelUpd = true;
                        }
                    }
                } else {
                    for (let i = 0; i < arr_index.length; i++) {
                        var dt = moment(this.vData.lRows[arr_index[i]].outDateTime).format('YYYY-MM-DD');
                        if (dt == adj.dt_date) {
                            this.vData.lRows[arr_index[i]].adjusts.push(adj);
                            this.vData.lRows[arr_index[i]].labelUpd = true;
                        }
                    }
                }
            }
        },

        validate() {
            if (this.adjType == oData.ADJ_CONS.JE && this.vRow.hasCheckIn) {
                oGui.showError("El renglón tiene checada de entrada, no es necesario justificarla");
                return false;
            }

            if (this.adjType == oData.ADJ_CONS.JS && this.vRow.hasCheckOut) {
                oGui.showError("El renglón tiene checada de salida, no es necesario justificarla");
                return false;
            }

            if (this.adjType == oData.ADJ_CONS.OR && !this.vRow.entryDelayMinutes > 0) {
                oGui.showError("No existe retardo para este día, no es necesario el ajuste");
                return false;
            }

            if (this.adjType == oData.ADJ_CONS.JF && !this.vRow.hasAbsence) {
                oGui.showError("El empleado no tiene falta este día, no es necesario justificarla");
                return false;
            }

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

            if (this.comments.length == 0) {
                oGui.showError("Debe ingresar un comentario");
                return false;
            }

            return true;
        },
        deleteAdjust(oAdj) {
            oGui.showLoading(3000);

            let route = '../prepayrolladjust/' + oAdj.id;

            axios.delete(route)
                .then(res => {
                    let oRes = res.data;

                    if (oRes.success) {
                        this.vRow.labelUpd = true;
                        for (var i = 0; i < this.vRow.adjusts.length; i++) {
                            if (this.vRow.adjusts[i].id == oRes.data.id) {
                                this.vRow.adjusts.splice(i, 1);
                            }
                        }
                        // this.setRowAdjusts();
                        this.rowAdjusts = this.vRow.adjusts;
                        this.comments = "";
                        oGui.showOk();
                    } else {
                        oGui.showError(oRes.msg);
                    }
                })
                .catch(function(error) {
                    console.log(error);
                });
        },
        showModal(oRow, index, isComment) {
            $('#comentFrec').val('').trigger('change');
            this.vRow = oRow;
            this.indexRow = index;
            // this.setRowAdjusts();
            this.rowAdjusts = oRow.adjusts;
            this.adjType = 1;
            this.minsEnabled = false;
            this.overMins = 0;
            this.comments = "";
            this.selComment = "";
            this.adjCategory = 0;
            this.inDateTime = "";
            this.outDateTime = "";
            this.isModifIn = false;
            this.isModifOut = false;
            this.isAdjustsDisabled = false;
            this.isDisabledByComments = false;
            this.dateInit = null;
            this.dateEnd = null;

            if (isComment) {
                this.adjCategory = 2;
                this.adjType = oData.ADJ_CONS.COM;
                this.isDisabledByComments = true;
            }

            let sIn = this.vRow.inDateTime.length == 10 ? this.vRow.inDateTime + " 00:00" : this.vRow.inDateTime.replace('   ', ' ');
            let sOut = this.vRow.outDateTime.length == 10 ? this.vRow.outDateTime + " 00:00" : this.vRow.outDateTime.replace('   ', ' ');

            let inD = moment(sIn);
            let outD = moment(sOut);

            this.inDateTime = inD.format('YYYY-MM-DDTHH:mm');
            this.outDateTime = outD.format('YYYY-MM-DDTHH:mm');

            this.onAdjustChange();

            if (this.vData.isPrepayrollInspection) {
                var checkVobo = document.getElementById('cb' + oRow.numEmployee);
                this.isAdjustsDisabled = checkVobo.checked;
            } else {
                this.isAdjustsDisabled = false;
            }

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
        getAdjToRow(oRow, index) {
            let labels = "";
            for (const adj of oRow.adjusts) {
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

            if (oRow.labelUpd || !(oRow.tempLabels === undefined)) {
                labels += "¡Pendiente de actualizar!";
            }

            if (oRow.isModifiedIn) {
                labels += "Entrada manual. ";
            }

            if (oRow.isModifiedOut) {
                labels += "Salida manual. ";
            }

            return labels;
        },
        onAdjustChange() {
            this.dateInit = null;
            this.dateEnd = null;
            if (this.adjCategory == "2") {
                this.comments = "";
                this.adjType = oData.ADJ_CONS.COM;
                this.adjTypeEnabled = false;
            } else {
                this.adjType = oData.ADJ_CONS.JE;
                this.adjTypeEnabled = true;
                this.comments = "";
            }

            if (
                this.adjType == oData.ADJ_CONS.DHE ||
                this.adjType == oData.ADJ_CONS.AHE
            ) {
                this.minsEnabled = true;
            } else {
                this.minsEnabled = false;
            }

            this.lComments = Object.keys(this.vData.lCommentsAdjsTypes).length > 0 ? this.vData.lCommentsAdjsTypes[this.adjType.toString()] : [];
            this.selComment = "";
            $("#comentFrec").val("").trigger("change");
        },
        onTypeChange() {
            this.minsEnabled = this.adjType == oData.ADJ_CONS.DHE || this.adjType == oData.ADJ_CONS.AHE;
            this.overMins = 0;
            this.lComments = Object.keys(this.vData.lCommentsAdjsTypes).length > 0 ? this.vData.lCommentsAdjsTypes[this.adjType.toString()] : [];
            this.selComment = "";
            $('#comentFrec').val('').trigger('change');
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
                    this.vRow.tempLabels = "Entrada y/o salida manuales.";
                    this.vData.lRows.push('refresh');
                    this.vData.lRows.pop();
                    oGui.showOk();
                })
                .catch(function(error) {
                    console.log(error);
                });
        },
        addComment() {
            this.comments = this.comments.length > 0 ? this.comments + ' ' + this.selComment : this.selComment;
        },
        addPreviusComment() {
            this.previusComment = "";
            var idEmployee = this.vData.lRows[this.indexRow].idEmployee;
            if (this.indexRow != 0) {
                if (this.vData.lRows[this.indexRow - 1].idEmployee == idEmployee) {
                    var adjusts = this.vData.lRows[this.indexRow - 1].adjusts;
                    var previusComment = "";
                    var hasComment = false;
                    for (let i = 0; i < adjusts.length; i++) {
                        if (adjusts[i].adjust_type_id == 7) {
                            hasComment = true;
                            previusComment =
                                previusComment.length > 0 ?
                                previusComment + " " + adjusts[i].comments :
                                previusComment + adjusts[i].comments;
                        }
                    }
                    if (previusComment.length > 0 && hasComment == true) {
                        this.comments =
                            this.comments.length > 0 ?
                            this.comments + " " + previusComment :
                            this.comments + previusComment;
                        this.adjType = 7;
                        this.newAdjust();
                        this.adjType = 1;
                        this.adjCategory = 0;
                        this.selComment = "";
                    } else {
                        oGui.showMessage(
                            "",
                            "No existe comentario de tipo comentario en el dia anterior",
                            "warning"
                        );
                    }
                } else {
                    oGui.showMessage(
                        "",
                        "No existe comentario de tipo comentario en el dia anterior",
                        "warning"
                    );
                }
            } else {
                oGui.showMessage(
                    "",
                    "No existe comentario de tipo comentario en el dia anterior",
                    "warning"
                );
            }
        },
        /**
         * Setea en el array global de comentarios, los comentarios hechos para el empleado actual
         * 
         * @param integer idEmployee 
         */
        getResumeComments(idEmployee) {
            this.resumeComments = [];
            let lEmps = Object.values(this.vData.lEmployees);
            for (const oEmployeeRow of lEmps) {
                if (oEmployeeRow.id == idEmployee) {
                    this.resumeComments = oEmployeeRow.comments;
                    this.nameEmployee = oEmployeeRow.name;
                    break;
                }
            }

            $('#commentsModal').modal('show');
        },
        /**
         * Invoca al método externo que se encarga de manejar el onChange del checkbox
         * 
         * @param $event event 
         * @param integer num 
         */
        onChangeVoboResume(event, num) {
            handleChangeCheck(event, num);
        },
        /**
         * Determina si el empleado ya ha sido revisado en la nómina actual.
         * Retorna un objeto de tipo VoBo si el Visto Bueno ha sido otorgado, si no,
         * retorna un undefined
         * 
         * @param integer numEmployee 
         * 
         * @returns Object
         */
        isVoboResume(numEmployee) {
            let vobo = this.vData.lEmpVobos[parseInt(numEmployee, 10)];

            return vobo;
        }
    },
})