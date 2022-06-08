var app = new Vue({
    el: '#incidentsEmployees',
    data: {
      vueData: oServerData,
      lIncidents: oServerData.lIncidents,
      employee: null,
      date: null,
      employee_id: null,
      selIncident: null,
      onSubmit: false
    },
    mounted(){
        
    },
    methods: {
        showModal(id, name, date){
            this.employee_id = id;
            this.employee = name;
            this.date = date;
            $('#incidentsModal').modal('show');
        },
        store(){
            if(this.selIncident != null){
                this.onSubmit = true;
                $('#incidentForm').submit();
            }else{
                oGui.showError('Debe seleccionar una incidencia');
            }
        }
    }
})