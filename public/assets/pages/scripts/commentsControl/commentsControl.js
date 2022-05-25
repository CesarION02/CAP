var app = new Vue({
    el: '#commentsControl',
    data: {
      vueData: oServerData,
      lComments: [],
    },
    mounted(){
        this.lComments = this.vueData.lComments;
    },
    methods: {
        updateComment(event, id, ruta){
            var check = event.target;
            var route = ruta.replace(':id', id);
            axios.post(route, {
                value: check.checked,
                id: id
            })
            .then(function (response) {
                oGui.showOk();
            })
            .catch(function (error) {
                oGui.showError('Error al actualizar el registro');
            });
        }
    }
  })