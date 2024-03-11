<script>
    function swalUsersNotVobo(idVobo, text, textInactive){
        swal({
            title: "¿Continuar con el visto bueno?",
            text: "Si se da visto bueno los usuarios: " + text + " no podrán dar visto bueno",
            icon: "warning",
            buttons: {
                confirm : {text:'Continuar', className:'sweet-warning'},
                cancel : 'Cancelar'
            },
        })
        .then((acepted) => {
            if (acepted) {
                if(textInactive.length > 0){
                    swalUsersNotVoboInactives(idVobo, textInactive);
                }else{
                    submitFormVobo(idVobo);
                }
            } else {
                swal("No se ha dado el visto bueno");
            }
        });
    }

    function swalUsersNotVoboInactives(idVobo, textInactive){
        swal({
            title: "¿Continuar con el visto bueno?",
            
            icon: "warning",
            buttons: {
                confirm : {text:'Continuar', className:'sweet-warning'},
                cancel : 'Cancelar'
            },
            content: {
                element: "strong",
                attributes: {
                    innerHTML: "Los usuarios: " + textInactive + " están inactivos, ¿desea continuar con el visto bueno?",
                },
            },
        })
        .then((acepted) => {
            if(acepted){
                submitFormVobo(idVobo);
            }else{
                swal("No se ha dado el visto bueno");
            }
        })
    }

    function submitFormVobo(idVobo){
        // let canSkipElement = document.getElementById('can_skip_id');
        // canSkipElement.value = 1;
        document.getElementById('form_vobo').submit();
    }

    function checkGroup(prepayReportControlId, sFormName) {
        var value = '<?php echo $idPreNomina; ?>';
        var text = '';
        var textInactive = '';
        $.ajax({
            type:'POST',
            url:'{{ $routeChildren }}',
            data:{ idprenomina: value, id: prepayReportControlId, _token: '{{ csrf_token() }}' },
            success:function(data) {
                if (data.users.length > 0) {
                    for (var i = 0; i < data.users.length; i++) {
                        if(data.users[i].is_active == 1){
                            text = text + data.users[i].name + ', ';
                        }else{
                            textInactive = textInactive + data.users[i].name + ', ';
                        }
                    }
                    // Se comenta confirmación por solicitud de Sergio Flores: no se puede dar Vobo si los usuarios que dependen de ti no han dado vobo.

                    if (data.bCanSkip != undefined && data.bCanSkip) {
                        if (text.length > 0) {
                            swalUsersNotVobo(prepayReportControlId, text, textInactive);
                        }else if(textInactive.length > 0){
                            swalUsersNotVoboInactives(prepayReportControlId, textInactive);
                        }else{
                            submitFormVobo(prepayReportControlId);
                        }
                    }else if(text.length == 0){
                        if(textInactive.length > 0){
                            swalUsersNotVoboInactives(prepayReportControlId, textInactive);
                        }
                    }else {
                        swal("¡Error!", "Los usuarios: " + text + " no han dado el visto bueno.", "error");
                    }
                    
                    // let message = "";
                    // if (data.users.length == 1) {
                    //     message = "El usuario: " + text + " no ha dado el visto bueno.";
                    // }
                    // else {
                    //     message = "Los usuarios: " + text + " no han dado el visto bueno."
                    // }
                    // swal("¡Error!", message, "error");
                    // if (sFormName == null) {
                    //     return false;
                    // }
                }
                else {
                    if (sFormName != null && sFormName.length > 0) {
                        document.getElementById('form_vobo').submit();
                    }
                    else {
                        return true;
                    }
                }
            }
        });

        return false;
    }
</script>