var app = new Vue({
    el: '#comments',
    data: {
      vueData: oServerData,
      lComments: [],
      comment: null,
      route: null
    },
    mounted(){
        this.lComments = this.vueData.lComments;
    },
    methods: {
        showModal(ruta){
            this.route = ruta;
            $('#commentModal').modal('show');
        },
        addNewComment(ruta){
            this.comment = null;
            this.showModal(ruta);
        },
        storeComment(){
            var route = this.route;
            axios.post(route, {
                comment: this.comment
            })
            .then(response => {
                window.location.reload(true);
                oGui.showOk();
            })
            .catch(function (error) {
                oGui.showError('Error al guardar el registro');
            });
        },
        editComment(id, comment, ruta){
            this.comment = comment;
            var route = ruta.replace(':id', id);
            this.showModal(route);
        },
        deleteComment(id, comment, ruta){
            var route = ruta.replace(':id', id);
            (async () => {
                if (await oGui.confirm('Desea eliminar:',comment,'warning')) {
                    axios.delete(route, {
                    })
                    .then(response => {
                        oGui.showOk();
                        window.location.reload(true);
                    })
                    .catch(function (error) {
                        oGui.showError('Error al guardar el registro');
                    });
                }
            })();
        },
        recoverComment(id, comment, ruta){
            var route = ruta.replace(':id', id);
            (async () => {
                if (await oGui.confirm('Desea recuperar:',comment,'warning')) {
                    axios.put(route, {
                    })
                    .then(response => {
                        oGui.showOk();
                        window.location.reload(true);
                    })
                    .catch(function (error) {
                        oGui.showError('Error al guardar el registro');
                    });
                }
            })();
        }
    }
})