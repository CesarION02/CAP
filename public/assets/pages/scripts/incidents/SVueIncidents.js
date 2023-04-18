var app = new Vue({
    el: '#incidentsApp',
    data: {
      showHoliday: false,
      commentRequired: false,
      showSubtypes: false,
      isEditing: oGlobalData.isEditing,
      lEmployees: oGlobalData.lEmployees,
      lIncidentTypes: oGlobalData.lIncidentTypes,
      lSubTypes: oGlobalData.lSubTypes,
      lCommControl: oGlobalData.lCommControl,
      iIncidentType: oGlobalData.incidentTypeId,
      iSubTypeId: oGlobalData.iSubTypeId,
      lCurrentSubtypes: []
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

            this.lCurrentSubtypes = [];
            for (const oType of this.lIncidentTypes) {
                if (oType.id == iIncidentType) {
                    this.showSubtypes = oType.has_subtypes;
                    if (this.showSubtypes) {
                        for (const oSubType of this.lSubTypes) {
                            if (oSubType.incident_type_id == iIncidentType) {
                                this.lCurrentSubtypes.push(oSubType);
                            }
                        }
                    }
                    break;
                }
            }
        },
        addComment() {
            let comment = ($('#comentarios').val().length > 0 ? '\n' : '') + $('#comentFrec').val();

            $('#comentarios').val($('#comentarios').val() + comment);
        }
    },
  })