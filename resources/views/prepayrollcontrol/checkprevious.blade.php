<script>
    function checkPrevius(id) {
        let ogui = new SGui();
        ogui.showLoading(5000);
        var value = '<?php echo $idPreNomina; ?>';
        var section = '';
        if(value == "week"){
            section = "semana";
        }else if(value = "biweek"){
            section = "quincena";
        }
        $.ajax({
            type:'POST',
            url:'{{ $routePrev }}',
            data:{ idprenomina: value, id: id, _token: '{{csrf_token()}}' },
            success:function(data) {
                if(data.previus == 0){
                    swal({
                        title: "Continuar con visto bueno?",
                        text: "La "+section+" anterior no tiene visto bueno",
                        icon: "warning",
                        buttons: {
                            confirm : {text:'Continuar', className:'sweet-warning'},
                            cancel : 'Cancelar'
                        },
                        // dangerMode: true,
                    })
                    .then((acepted) => {
                        if (acepted) {
                            checkGroup(id, "form_vobo");
                        } else {
                            swal("No se ha dado el visto bueno");
                        }
                    });
                }else{
                    checkGroup(id, "form_vobo");
                }
            }
        });
    }
</script>