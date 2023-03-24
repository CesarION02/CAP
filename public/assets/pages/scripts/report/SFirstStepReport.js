var app = new Vue({
    el: "#incidentsEmployees",
    data: {
        vueData: oServerData,
        vueGui: oGui,
        lTypeIncidents: oServerData.lTypeIncidents,
        lTypeCapIncidents: oServerData.lTypeCapIncidents,
        lTypeIncidentsList: [],
        vRows: oServerData.lRows,
        vDates: oServerData.aDates,
        oEvent: {},
        employee: null,
        date: null,
        employee_id: null,
        selIncident: null,
        oldIncident: null,
        onSubmit: false,
        canDelete: false,
        isDisabled: false
    },
    mounted() {
    },
    methods: {
        /**
         * 
         * @param int id 
         * @param string name 
         * @param string date 
         * @param array aEvents 
         */
        showModal(idEmployee, name, date, aEvents, hasAbsence, isVobo) {
            if (hasAbsence) {
                return;
            }

            this.employee = name;
            this.date = date;

            if (Array.isArray(aEvents) && aEvents.length > 0) {
                this.oEvent = aEvents[0];
                this.oldIncident = this.oEvent.type_id;
                this.canDelete = true;
            }
            else {
                if (isVobo) {
                    return;
                }

                this.oEvent = { 
                                type_id: null,
                                employee_id: idEmployee,
                                start_date: date,
                                id: 0,
                                is_external: false
                            };
                this.oldIncident = null;
                this.canDelete = false;
            }

            this.isDisabled = this.oEvent.is_external || isVobo;
            if (this.isDisabled) {
                this.lTypeIncidentsList = this.lTypeIncidents;
            }
            else {
                this.lTypeIncidentsList = this.lTypeCapIncidents;
            }

            $("#incidentsModal").modal("show");
        },
        store() {
            if (this.oEvent.type_id != null && this.oEvent.type_id != 0) {
                oGui.showLoading(15000);
                this.onSubmit = true;
                $("#incidentForm").attr("action", this.vueData.routeStore);
                $("#incidentForm").submit();
            }
            else {
                oGui.showError("Debe seleccionar una incidencia");
            }
        },
        deleteIncident() {
            this.onSubmit = true;
            $("#incidentForm").attr("action", this.vueData.routeDelete);
            $("#incidentForm").submit();
        },
        /**
            id: name, is_agreement, is_allowed
            1: INASIST. S/PERMISO, [0][0]
            2: INASIST. C/PERMISO S/GOCE, [0][0]
            3: INASIST. C/PERMISO C/GOCE, [0][1]
            4: INASIST. ADMTIVA. RELOJ CHECADOR, [0][0]
            5: INASIST. ADMTIVA. SUSPENSIÓN, [0][0]
            6: INASIST. ADMTIVA. OTROS, [0][0]
            7: ONOMÁSTICO, [0][1]
            8: Riesgo de trabajo, [0][1]
            9: Enfermedad en general, [0][0]
            10: Maternidad, [0][1]
            11: Licencia por cuidados médicos de hijos diagnosticados con cáncer., [0][1]
            12: VACACIONES, [0][1]
            13: VACACIONES PENDIENTES, [0][1]
            14: CAPACITACIÓN, [1][1]
            15: TRABAJO FUERA PLANTA, [1][1]
            16: PATERNIDAD, [0][1]
            17: DIA OTORGADO, [1][1]
            18: INASIST. PRESCRIPCION MEDICA, [0][1]
            19: DESCANSO, [1][1]
            20: INASIST. TRABAJO FUERA DE PLANTA, [1][1]
            21: VACACIONES, [1][1]
            22: INCAPACIDAD, [1][1]
            23: ONOMÁSTICO, [1][1]
            24: PERMISO, [1][0]

            * @param array aEvents 
            * @returns string nombre de la clase que será desplegada en la celda
         */
        getCssClass(aEvents, hasAbsence) {
            if (hasAbsence) {
                return 'falta';
            }

            if (Array.isArray(aEvents)) {
                if (aEvents.length > 0) {
                    let oEvent = aEvents[0];
                    switch (oEvent.type_id) {
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                        case 5:
                        case 6:
                        case 20:
                            return 'inasistencia';
                        case 12:
                        case 13:
                        case 21:
                            return 'vacaciones';
                        case 7:
                        case 23:
                            return 'onomastico';
                        case 19:
                            return 'descanso';
                        case 10:
                        case 11:
                        case 16:
                        case 18:
                        case 22:
                            return 'incapacidad';
                        case 8:
                            return 'no-permitida';
                        case 9:
                        case 14:
                        case 15:
                        case 17:                        
                        case 23:
                        case 24:
                            return 'permitida';
                        case 25:
                        case 26:
                        case 27:
                            return 'default';
                        default:
                            break;
                    }
                }
            }

            return '';
        },
        getText(aEvents, hasAbsence) {
            if (hasAbsence) {
                return 'FALTA';
            }
            if (Array.isArray(aEvents)) {
                if (aEvents.length > 0) {
                    let oEvent = aEvents[0];
                    let sExternal = oEvent.is_external ? "\n(siie)" : "";
                    switch (oEvent.type_id) {
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                        case 5:
                        case 6:
                            return 'INASISTENCIA' + sExternal;
                        case 12:
                        case 13:
                            return 'VACACIONES' + sExternal;
                        case 7:
                        case 23:
                            return 'ONOMÁSTICO' + sExternal;
                        case 19:
                            return 'DESCANSO' + sExternal;
                        case 10:
                        case 11:
                        case 16:
                        case 18:
                        case 22:
                            return 'INCAPACIDAD' + sExternal;
                        default:
                            return oEvent.type_name.toUpperCase() + sExternal
                    }
                }
            }

            return '';
        },
        getTitle(aEvents, hasAbsence) {
            if (hasAbsence) {
                return 'Falta';
            }
            if (Array.isArray(aEvents)) {
                if (aEvents.length > 0) {
                    let oEvent = aEvents[0];
                    
                    return oEvent.type_name + (!!oEvent.nts ? (" - " + oEvent.nts) : "");
                }
            }

            return '';
        }
    },
});
