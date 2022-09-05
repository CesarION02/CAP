var app = new Vue({
    el: '#areaPrograming',
    data: {
        datas: oData.datas,
        areaSelect: null,
        scheduleSelect: null,
        start_date: null,
        storeRoute: oData.storeRoute,
        getAreaProgRoute: oData.getAreaProgRoute,
        indexRoute: oData.indexRoute,
    },
    mounted(){
        if(this.datas != null){
            this.areaSelect = this.datas.area_id; 
            this.scheduleSelect = this.datas.schedule_template_id; 
            this.start_date = this.datas.start_date; 
        }
        let self = this;
        $('#area')
            .on('select2:select', function (e){
                self.areaSelect = e.params.data.id;
            });

        $('#horario1')
            .on('select2:select', function (e){
                self.scheduleSelect = e.params.data.id;
            });
    },
    methods: {
        getAreaProgamming(){
            axios.post(this.getAreaProgRoute, {
                areaId: this.areaSelect,
            })
            .then(res => {
                var data = res.data;
                if(!data.has_schedule){
                    this.store();
                } else {
                    (async () => {
                        if (await oGui.confirm('El area ya tiene un horario asignado','Desea reemplazarlo?','warning')) {
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
                areaId: this.areaSelect,
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