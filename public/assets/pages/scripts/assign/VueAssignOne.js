var app = new Vue({
    el: '#assingOneApp',
    data: {
        vueServerData: oServerData,
        vueDateAssigns: [],
        dtDate: (new Date()).toISOString().split('T')[0],
        bHoliday: false,
        iHolidayAux: 0,
        iHoliday: 0,
        sDescription: "",
        iEmployee: null,
        oToChange: null,
        iAssignament: null,
        iAction: 1, // 1: nuevo, 2: editar
        vueGui: oGui,
        sStartDate: oServerData.startDate,
        sEndDate: oServerData.endDate
    },
    methods: {
        setDates(startDate, endDate) {
            this.sStartDate = startDate;
            this.sEndDate = endDate;
        },
        /**
         * Muestra el modal cuando un registro nuevo será creado
         * pone como valor por default el sábado siguiente a la fecha actual
         */
        onShowModal() {
            this.iEmployee = null;
            
            const today = moment().isoWeekday();
            
            if (today < 6) {
                this.dtDate = (moment().isoWeekday(6)).toISOString().split('T')[0];
            }

            this.bHoliday = false;
            this.iHolidayAux = 0;
            this.iHoliday = 0;

            $("#sel_emp").val(0).trigger("chosen:updated");
            $("#st_date").attr('readonly', false);

            this.iAction = 1;

            $("#modalScheduleOne").modal();
        },
        
        /**
         * Muestra el modal de edición, inhabilitando el campo de fecha y carga los 
         * datos necesarios para la modificación del registro
         * 
         * @param {Assignament} schedule 
         */
        onShowEditModal(schedule) {
            this.iAction = 2;

            if (schedule.id > 0) {
                this.iEmployee = schedule.employee_id;
                this.dtDate = schedule.start_date;
                this.iAssignament = schedule.id;
                this.bHoliday = false;
                this.iHoliday = 0;
                this.iHolidayAux = 0;
                
                $("#sel_emp").val(this.iEmployee).trigger("chosen:updated");
            }
            else {
                this.bHoliday = true;
                this.iHoliday = schedule.holiday_id;
                this.sDescription = schedule.text_description;

                $("#sel_hol").val(this.iHoliday).trigger("chosen:updated");
                this.iHolidayAux = schedule.h_id;
            }
            
            $("#is_holiday").attr('disabled', true);
            $("#st_date").attr('readonly', true);

            $("#modalScheduleOne").modal();
        },

        /**
         * Cuando se cierra el modal este método es llamado para determinar si se
         * realizará una creación o una edición de registro
         */
        processAssignament() {
            switch (this.iAction) {
                case 1:
                    if (! this.bHoliday) {
                        this.newAssignament();
                    }
                    else {
                        this.newHolidayAux();
                    }

                    break;
                case 2:
                    if (! this.bHoliday) {
                        this.editAssignament();
                    }
                    else {
                        this.editHolidayAux();
                    }
                    break;
            
                default:
                    break;
            }
            
        },

        /**
         * Método previo a la inserción del registro, determina si se hará la inserción
         * sencilla, el desplazamiento o se reemplazará el registro
         */
        newAssignament() {
            if (! this.validateDay()) {
                oGui.showError("Debe seleccionar solo días sábados.");
                return;
            }

            if (! this.validateEmployee()) {
                oGui.showError("Debe seleccionar un empleado.");
                return;
            }

            let res = 0;
            let oAssByDate = this.getAssignamentByDate(this.dtDate);
            if (oAssByDate != null) {
                swal({
                    title: "Ya existe una guardia programada para este día",
                    text: "¿Deseas desplazar las guardias una semana?",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        displace: {
                            text: "Desplazar guardias",
                            value: "displace",
                        },
                        replace: {
                            text: "Reemplazar guardia",
                            value: "replace",
                        },
                      },
                    dangerMode: true,
                })
                .then((value) => {
                    oGui.showLoading(3000);

                    switch (value) {
                    
                        case "replace":
                            this.oToChange = oAssByDate;
                            res = 1;
                        break;
                    
                        case "displace":
                            this.oToChange = oAssByDate;
                            res = 2;
                        break;
                    
                        default:
                        res = -1;
                    }

                    this.postNew(res);

                    $("#modalScheduleOne").modal("hide");
                });
            }
            else {
                oGui.showLoading(3000);
                
                this.postNew(res);
                $("#modalScheduleOne").modal("hide");
            }
        },

        /**
         * Valida que un empleado haya sido seleccionado en el modal y si esto es así
         * realiza la modificación del registro (solo puede modificarse el empleado)
         */
        editAssignament() {
            if (! this.validateEmployee()) {
                oGui.showError("Debe seleccionar un empleado.");
                return;
            }

            oGui.showLoading(3000);

            let route = './assignone/' + this.iAssignament;

            axios.put(route, {
                emply_id: this.iEmployee
            })
            .then(res => {
                console.log(res);

                this.reloadResources(res.data);

                $("#modalScheduleOne").modal("hide");
                oGui.showOk();
            })
            .catch(function(error) {
                console.log(error);
            });
        },

        /**
         * realiza la petición al servidor para insertar el nuevo registro,
         * también envía la acción que se realizará:
         * -1: cancelar
         * 0: insertar guardia
         * 1: remplazar guardia
         * 2: desplazar guardias
         * 
         * @param {integer} res 
         */
        postNew(res) {
            if (res == -1) {
                return;
            }

            let oAssign = null;
            oAssign = new Assignament();

            oAssign.id = 0;
            oAssign.department_id = null;
            oAssign.employee_id = this.iEmployee;
            oAssign.start_date = this.dtDate;
            oAssign.end_date = this.dtDate;
            oAssign.order_gs = 0;

            let route = './assignone';
   
            axios.post(route, {
                i_action: res,
                ass_objs: JSON.stringify(oAssign),
                to_change: JSON.stringify(this.oToChange),
                template: oServerData.iTemplateId,
                group_schedule: oServerData.iGrpSchId,
            })
            .then(res => {
                console.log(res);

                this.reloadResources(res.data);

                oGui.showOk();
            })
            .catch(function(error) {
                console.log(error);
            });
        },

        /**
         * Pide al usuario una confirmación para poder eliminar el registro
         * 
         * @param {SAssignament} oAssi 
         */
        prevDeleteAssignament(oAssi) {
            swal({
                title: "¿Seguro?",
                text: "Está por eliminar la guardia del día " + oAssi.start_date,
                icon: "warning",
                buttons: true,
                dangerMode: true,
              })
              .then((willDelete) => {
                if (willDelete) {
                    oGui.showLoading(3000);

                    this.deleteAssignament(oAssi);
                }
            });
        },

        /**
         * Realiza la petición al servidor para 
         * 
         * @param {SAssignament} oAssignament 
         */
        deleteAssignament(oAssignament) {
            let route = '';

            if (oAssignament.id > 0) {
                route = './assignone/' + oAssignament.id;
            }
            else {
                route = './holidaysaux/' + oAssignament.h_id;
            }

            axios
            .delete(route)
            .then(res => {
                let obj = res.data;

                this.reloadResources(obj);

                oGui.showOk();
            })
            .catch(function(error) {
                console.log(error);
            });
        },

        /**
         * Obtiene un objeto Assignament si hay una correspondencia con la fecha recibida,
         * si no la hay retorna un null
         * 
         * @param {string} sDate 
         */
        getAssignamentByDate(sDate) {
            let assign = this.vueDateAssigns[sDate];
            return assign == undefined ? null : assign;
        },

        /**
         * Valida que la fecha seleccionada corresponda a un día sábado
         */
        validateDay() {
            return moment(this.dtDate).isoWeekday() == 6;
        },

        /**
         * Valida que un empleado haya sido seleccionado
         */
        validateEmployee() {
            this.iEmployee = $('#sel_emp')[0].value;

            return this.iEmployee > 0;
        },

        newHolidayAux() {
            this.iHoliday = $('#sel_hol')[0].value;

            if (! this.validateDay()) {
                oGui.showError("Debe seleccionar solo días sábados.");
                return;
            }

            oGui.showLoading(3000);

            let oHolidayAux = new SHolidayAux();

            oHolidayAux.dt_date = this.dtDate;
            oHolidayAux.holiday_id = this.iHoliday;
            oHolidayAux.text_description = this.sDescription;

            let route = './holidaysaux';

            axios.post(route, {
                hol_aux: JSON.stringify(oHolidayAux),
            })
            .then(res => {
                console.log(res);

                this.reloadResources(res.data);

                oGui.showOk();
            })
            .catch(function(error) {
                console.log(error);
            });
        },

        editHolidayAux() {
            oGui.showLoading(3000);

            let route = './holidaysaux/' + this.iHolidayAux;

            axios.put(route, {
                holiday_id: this.iHoliday,
                text_description: this.sDescription,
            })
            .then(res => {
                console.log(res);

                this.reloadResources(res.data);

                $("#modalScheduleOne").modal("hide");
                oGui.showOk();
            })
            .catch(function(error) {
                console.log(error);
            });
        },

        refresh() {
            oGui.showLoading(3000);

            let route = './assignonedata';

            axios
            .get(route)
            .then(res => {
                let obj = res.data;

                this.reloadResources(obj);
            })
            .catch(function(error) {
                console.log(error);
            });
        },

        reloadResources(lList) {
            if (Array.isArray(lList)) {
                this.vueServerData.lSchedules = lList;
                if (this.vueServerData.lSchedules != undefined && this.vueServerData.lSchedules.length > 0) {
                    let auxArray = new Array();
            
                    for (const assign of this.vueServerData.lSchedules) {
                        auxArray[assign.start_date + ""] = assign;
                    }
            
                    this.vueDateAssigns = auxArray;
    
                }
            }
            else {
                location.reload();
            }    

        }
    },

    /**
     * Crea una lista de los horarios asignados utilizando como llave la fecha de la programación
     */
    created() {
        if (this.vueServerData.lSchedules != undefined && this.vueServerData.lSchedules.length > 0) {
            let auxArray = new Array();
    
            for (const assign of this.vueServerData.lSchedules) {
                auxArray[assign.start_date + ""] = assign;
            }
    
            this.vueDateAssigns = auxArray;
        }
    }
  })