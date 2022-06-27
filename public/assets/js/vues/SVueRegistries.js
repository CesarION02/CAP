var app = new Vue({
    el: '#divRegistries',
    data: {
        message: 'Hello Vue!',
        isSingle: true,
        picked: 'single',
        employee: 0,
        date: null,
        time: null,
        type: 0,
        lRegistries: [],
        lIncidents: [],
        canCheck: false,
        messageChecks: "",
    },
    monuted() {
        
    },
    methods: {
        onTypeChange() {
            this.isSingle = this.picked == 'single';
        },
        getChecks(route){
            this.messageChecks = "";
            if(this.employee != 0 && this.date != null){
                axios.post(route, {
                    employee_id: this.employee,
                    date: this.date
                })
                .then(response => {
                    this.lRegistries = response.data.lRegistries;
                    this.lIncidents = response.data.lIncidents;
                    var haveEntry = false;
                    var haveDeparture = false;
                    for (let i = (this.lRegistries.length - 1); i >= 0; i--) {
                        this.lRegistries[i].type_id == 1 ? haveEntry = true : '';
                        this.lRegistries[i].type_id == 2 ? haveDeparture = true : '';
                    }
                    if(!haveEntry && !haveDeparture){
                        this.messageChecks = "No existen checadas del dia";
                    }else if(!haveEntry){
                        this.messageChecks = "Falta entrada para completar el dia";
                    }else if(!haveDeparture){
                        this.messageChecks = "Falta salida para completar el dia";
                    }
                    this.canCheck = (this.date != null && this.employee != 0) ? true : false;
                    if(this.lRegistries.length > 0){
                        oGui.showOk();
                    }else{
                        oGui.showMessage('Realizado','No se encontraron registros','success');
                    }
                })
                .catch(function (error) {
                    oGui.showError('Error al consultar los registros');
                });
            }else{
                oGui.showError('Debe seleccionar empleado y fecha');
            }
        },
        store(){
            if(this.employee != 0){
                if(this.date != null){
                    if(this.time != null){
                        if(this.picked == 'single'){
                            var checkType = "";
                            switch (this.type) {
                                case 1:
                                    if(this.lRegistries.length > 0){
                                        for (let i = (this.lRegistries.length - 1); i >= 0; i--) {
                                            if(this.lRegistries[i].type_id == 1){
                                                checkType = "entrada";
                                                break;
                                            }
                                        }
                                    }
                                    break;
                                case 2:
                                    if(this.lRegistries.length > 0){
                                        for (let i = (this.lRegistries.length - 1); i >= 0; i--) {
                                            if(this.lRegistries[i].type_id == 1){
                                                checkType = "salida";
                                                break;
                                            }
                                        }
                                    }
                                    break;
                                case 0:
                                    oGui.showError('Debe seleccionar tipo checada');
                                    break;
                                default:
                                    break;
                            }
                            if(checkType != ""){
                                (async () => {
                                    if (await oGui.confirm('Ya existe una '+ checkType,'Desea agregar una nueva?','warning')) {
                                        $('#form-general').submit();
                                    }
                                })();
                            }else{
                                $('#form-general').submit();
                            }
                        }else{
                            if(this.lRegistries.length > 0){
                                (async () => {
                                    if (await oGui.confirm('Ya existe una entrada/salida','Desea agregar una nueva?','warning')) {
                                        $('#form-general').submit();
                                    }
                                })();
                            }else{
                                $('#form-general').submit();
                            }
                        }
                    }else{
                        oGui.showError('Debe seleccionar una hora');    
                    }
                }else{
                    oGui.showError('Debe seleccionar una fecha');    
                }
            }else{
                oGui.showError('Debe seleccionar un empleado');
            }
        },
        resetCreate(){
            this.isSingle = true;
            this.picked = 'single';
            this.employee = 0;
            this.date = null;
            this.time = null;
            this.type = 0;
            this.lRegistries = [];
            this.canCheck = false;
            this.messageChecks = "";
            $('#selEmployee').val(0).trigger('chosen:updated');
        }
    },
  })