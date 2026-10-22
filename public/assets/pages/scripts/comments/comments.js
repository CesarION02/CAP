var app = new Vue({
    el: '#comments',
    data: {
      vueData: oServerData,
      lComments: [],
      comment: null,
      route: null,
      modalDisabled: false,
      selectedAdjustTypes: [],
      lAdjustTypes: []
    },
    mounted(){
        this.lComments = this.vueData.lComments;
        this.lAdjustTypes = this.vueData.adjustTypes;
        this.initSelect2();
    },
    methods: {
        initSelect2() {
            let vm = this;
        
            this.$nextTick(() => {
              $('.js-example-basic-multiple').select2({
                width: 'resolve'
              });
        
              // Sincroniza select2 con Vue
              $('.js-example-basic-multiple').on('change', function () {
                vm.selectedAdjustTypes = $(this).val().map(Number); // ← convierte a números si los IDs son enteros
              });
            });
          },

        showModal(ruta){
            this.route = ruta;
            $('#commentModal').modal('show');
        },
        addNewComment(ruta){
            this.comment = null;
            this.selectedAdjustTypes = [];
            this.showModal(ruta);
        },
        storeComment(){
            this.modalDisabled = true;
            var route = this.route;
            if(this.comment != null && this.comment != ''){
                axios.post(route, {
                    comment: this.comment,
                    types: this.selectedAdjustTypes
                })
                .then(response => {
                    if(response.data.success){
                        window.location.reload(true);
                        oGui.showOk();
                    }else{
                        this.modalDisabled = false;
                        oGui.showMessage('',response.data.message,response.data.icon);
                    }
                })
                .catch(function (error) {
                    this.modalDisabled = false;
                    oGui.showError('Error al guardar el registro');
                });
            }else{
                this.modalDisabled = false;
                oGui.showError('Debe ingresar un comentario');
            }
        },
        editComment(id, comment, ruta, types){
            this.comment = comment;
            this.selectedAdjustTypes = types;
            var route = ruta.replace(':id', id);
            this.$nextTick(() => {
                // Seleccionamos los valores y activamos el plugin select2
                $('.js-example-basic-multiple')
                    .val(this.selectedAdjustTypes)
                    .trigger('change');
        
                // Abrimos el modal
                this.showModal(route);
            });
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