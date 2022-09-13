var app = new Vue({
    el: '#usersApp',
    data: {
        datas: oData,
        userSelect: null,
        userName: null,
        userId: null,
    },
    mounted(){
        let self = this;
        $('#copyUser')
            .on('select2:select', function (e){
                self.userSelect = e.params.data.id;
            });
    },
    methods: {
        showCopyUser(user_id, user_name){
            this.userName = user_name;
            this.userId = user_id;
            $('#copyModal').modal('show');
        },

        storeCopyUser(){
            axios.post(this.datas.routeCopyUser, {
                'originUserId': this.userId,
                'destinationUserId': this.userSelect,
            })
            .then(res => {
                var data = res.data;
                if(data.success){
                    oGui.showMessage('Realizado',data.message, data.icon);
                }else{
                    oGui.showMessage('Error',data.message, data.icon);
                }
            })
            .catch(function(error) {
                console.log(error);
                oGui.showError('Error al guardar el registro');
            });
        }
    }
})