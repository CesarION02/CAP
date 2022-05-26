@extends("theme.$theme.layout")
@section('title')
    Panel de control de comentarios
@endsection

@section('content')
<div class="row" id="commentsControl">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Panel de control de comentarios</h3>
            </div>
            <div class="box-body">
                <button onclick="desSelAll('{{route('commentsControl_update', ':id')}}')" style="float: right;">Desmarcar todo</button>
                <button onclick="selAll('{{route('commentsControl_update', ':id')}}')" style="float: right;">Marcar todo</button>
                <br>
                <br>
                <table id="commentsControlTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Comentario</th>
                            <th>Revisar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="comment in lComments">
                            <td>@{{comment.Comment}}</td>
                            <td><input type="checkbox" name="mycheckbox" :checked="comment.value" v-on:change="updateComment($event, comment.id_commentControl, '{{route('commentsControl_update', ':id')}}')"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/filter.js")}}" type="text/javascript"></script>
<script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
<script>
    function ServerData () {
        this.lComments = <?php echo json_encode($lComments) ?>;
    }
    
    var oServerData = new ServerData();
    var oGui = new SGui();
</script>
<script src="{{ asset("assets/pages/scripts/commentsControl/commentsControl.js") }}" type="text/javascript"></script>
<script>
    function reloadTable() {
            let table = $('#commentsControlTable').DataTable({
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                "colReorder": true,
                "scrollX": true,
                "dom": 'Bfrtip',
                "lengthMenu": [
                    [ 10, 25, 50, 100, -1 ],
                    [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                ],
                "buttons": [
                        'pageLength',
                    {
                        extend: 'copy',
                        text: 'Copiar'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel'
                    },
                    {
                        extend: 'print',
                        text: 'Imprimir'
                    }
                ]
            });
    }
    reloadTable();
    
</script>
<script>
    function up(event, id){
        let checked = event.target;
    }

    function selAll(ruta){
        oGui.showLoading(3000);
        var table = $('#commentsControlTable').DataTable();
        table.$("input[type=checkbox]").prop("checked", true);
        var route = ruta.replace(':id', 'trueAll');
        axios.post(route, {
            value: true,
            id: 'trueAll'
        })
        .then(function (response) {
            oGui.showOk();
        })
        .catch(function (error) {
            oGui.showError('Error al actualizar el registro');
        });
    }

    function desSelAll(ruta){
        oGui.showLoading(3000);
        var table = $('#commentsControlTable').DataTable();
        table.$("input[type=checkbox]").prop("checked", false);
        var route = ruta.replace(':id', 'falseAll');
        axios.post(route, {
            value: true,
            id: 'falseAll'
        })
        .then(function (response) {
            oGui.showOk();
        })
        .catch(function (error) {
            oGui.showError('Error al actualizar el registro');
        });
    }
</script>
@endsection