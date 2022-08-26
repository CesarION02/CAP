var app = new Vue({
    el: '#incidentsEmployees',
    data: {
      vueData: oServerData,
      lIncidents: oServerData.lIncidents,
      employee: null,
      date: null,
      employee_id: null,
      selIncident: null,
      oldIncident: null,
      onSubmit: false
    },
    mounted(){
        
    },
    methods: {
        showModal(id, name, date, incident){
            this.employee_id = id;
            this.employee = name;
            this.date = date;
            const $options = Array.from(this.lIncidents);
            const result = $options.find(item => item.name === incident);
            if(result != undefined){
                this.selIncident = result.id;
                this.oldIncident = result.id;
            }else{
                this.selIncident = null;
                this.oldIncident = null;
            }
            $('#incidentsModal').modal('show');
        },
        store(){
            if(this.selIncident != null){
                oGui.showLoading(15000);
                this.onSubmit = true;
                $('#incidentForm').attr('action', this.vueData.routeStore);
                $('#incidentForm').submit();
            }else{
                oGui.showError('Debe seleccionar una incidencia');
            }
        },
        deleteIncident(){
            this.onSubmit = true;
            $('#incidentForm').attr('action', this.vueData.routeDelete);
            $('#incidentForm').submit();
        }
    }
})