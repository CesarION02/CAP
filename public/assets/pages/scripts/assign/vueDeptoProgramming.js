var app = new Vue({
    el: '#deptoPrograming',
    data: {
        datas: oData.datas,
        deptoSelect: null,
        scheduleSelect: null,
        start_date: null,
        storeRoute: oData.storeRoute,
        getDeptoProgRoute: oData.getDeptoProgRoute,
        indexRoute: oData.indexRoute,
    },
    mounted(){
        if(this.datas != null){
            this.deptoSelect = this.datas.department_id; 
            this.scheduleSelect = this.datas.schedule_template_id; 
            this.start_date = this.datas.start_date; 
        }
        let self = this;
        $('#depto')
            .on('select2:select', function (e){
                self.deptoSelect = e.params.data.id;
            });

        $('#horario1')
            .on('select2:select', function (e){
                self.scheduleSelect = e.params.data.id;
            });
    },
    methods: {
        getDeptoProgamming(){
            axios.post(this.getDeptoProgRoute, {
                deptoId: this.deptoSelect,
            })
            .then(res => {
                var data = res.data;
                if(!data.has_schedule){
                    this.store();
                } else {
                    (async () => {
                        if (await oGui.confirm('El departamento ya tiene un horario asignado','Desea reemplazarlo?','warning')) {
                            this.store();
                        }
                    })();
                }
            })
            .catch(function(error) {
                console.log(error);
                oGui.showError('Error al obtener el registro');
            })
        },

        store(){
            axios.post(this.storeRoute, {
                deptoId: this.deptoSelect,
                scheduleId: this.scheduleSelect,
                start_date: this.start_date,
            })
            .then(res => {
                var data = res.data;
                oGui.showMessage('', data.message ,data.icon);
                if(data.success){
                    window.location.href = this.indexRoute;
                }
            })
            .catch(function(error) {
                console.log(error);
                oGui.showError('Error al guardar el registro');
            });
        }
    }
})