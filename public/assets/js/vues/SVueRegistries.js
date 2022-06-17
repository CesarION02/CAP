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
        canCheck: false,
    },
    monuted() {
        
    },
    methods: {
        onTypeChange() {
            this.isSingle = this.picked == 'single';
        },
        getChecks(route){
            if(this.employee != 0 && this.date != null){
                axios.post(route, {
                    employee_id: this.employee,
                    date: this.date
                })
                .then(response => {
                    console.log(response);
                    this.lRegistries = response.data.lRegistries;
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
                            if(this.type != 0){
                                $('#form-general').submit();
                            }else{
                                oGui.showError('Debe seleccionar tipo checada');    
                            }
                        }else{
                            $('#form-general').submit();
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
            $('#selEmployee').val(0).trigger('chosen:updated');
        }
    },
  })