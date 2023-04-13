var app = new Vue({
    el: '#incidentsApp',
    data: {
      showHoliday: false,
      commentRequired: false,
      isEditing: oGlobalData.isEditing,
      lEmployees: oGlobalData.lEmployees,
      lCommControl: oGlobalData.lCommControl,
      iIncidentType: oGlobalData.incidentTypeId
    },
    mounted() {
        if (! this.isEditing) {
            $('#employee_id').select2();
        }
        $('#comentFrec').select2();
        $('#holiday_id').select2();

        this.onChangeIncidentType(this.iIncidentType);
    },
    updated() {
        switch (this.iIncidentType) {
            case "17":
                $("#holiday_id").select2();
                break;
        
            default:
                break;
        }
    },
    methods: {
        onChangeIncidentType(evt) {
            let iIncidentType = isNaN(evt) ? evt.target.value : (evt + "");
            switch (iIncidentType) {
                case "17":
                    this.showHoliday = true;
                    break;
            
                default:
                    this.showHoliday = false;
                    break;
            }

            this.commentRequired = this.lCommControl.includes(iIncidentType);
            this.iIncidentType = iIncidentType;
        },
        addComment() {
            let comment = ($('#comentarios').val().length > 0 ? '\n' : '') + $('#comentFrec').val();

            $('#comentarios').val($('#comentarios').val() + comment);
        }
    },
  })