var app = new Vue({
    el: '#subIncidentsApp',
    data: {
      message: "Hoola!",
      lIncidentTypes: oGlobalData.lIncidentTypes,
      updateAttrRoute: oGlobalData.updateAttrRoute
    },
    mounted() {
        
    },
    updated() {
        
    },
    methods: {
        onToggleChange(idType, atributteVar, newValue) {
            oGui.showLoading(5000);
            // alert(idType + ' ' + atributteVar + ' ' + newValue);

            axios({
                method: "put",
                url: this.updateAttrRoute,
                responseType: "json",
                data: {
                    id_inc_type: idType,
                    attribute_nm: atributteVar,
                    new_value: newValue
                },
            }).then(function (response) {
                console.log(response.data);
                if (response.data.errorInfo) {
                    oGui.showError(response.data.errorInfo.toString());
                    return;
                }
                
                this.lIncidentTypes = response.data;
                oGui.showOk();
            });
        }
    },
  })