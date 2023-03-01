<script>
    function checkGroup(prepayReportControlId, sFormName) {
        var value = '<?php echo $idPreNomina; ?>';
        var text = '';
        $.ajax({
            type:'POST',
            url:'{{ $routeChildren }}',
            data:{ idprenomina: value, id: prepayReportControlId, _token: '{{ csrf_token() }}' },
            success:function(data) {
                if (data.users.length > 0) {
                    for (var i = 0; i < data.users.length; i++) {
                        text = text + data.users[i] + ', ';
                    }
                    // Se comenta confirmación por solicitud de Sergio Flores: no se puede dar Vobo si los usuarios que dependen de ti no han dado vobo.

                    // if (data.bCanSkip != undefined && data.bCanSkip) {
                    //     swal({
                    //         title: "¿Continuar con el visto bueno?",
                    //         text: "Los usuarios: " + text + " no han dado el visto bueno",
                    //         icon: "warning",
                    //         buttons: {
                    //             confirm : {text:'Continuar', className:'sweet-warning'},
                    //             cancel : 'Cancelar'
                    //         },
                    //         // dangerMode: true,
                    //     })
                    //     .then((acepted) => {
                    //         if (acepted) {
                    //             let canSkipElement = document.getElementById('can_skip_id');
                    //             canSkipElement.value = 1;
                    //             document.getElementById('form_vobo').submit();
                    //         } else {
                    //             swal("No se ha dado el visto bueno");
                    //         }
                    //     });
                    // }
                    // else {
                    //     swal("¡Error!", "Los usuarios: " + text + " no han dado el visto bueno.", "error");
                    // }
                    
                    let message = "";
                    if (data.users.length == 1) {
                        message = "El usuario: " + text + " no ha dado el visto bueno.";
                    }
                    else {
                        message = "Los usuarios: " + text + " no han dado el visto bueno."
                    }
                    swal("¡Error!", message, "error");
                    if (sFormName == null) {
                        return false;
                    }
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