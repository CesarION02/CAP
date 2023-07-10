@extends("theme.$theme.layout")
@section('title')
    Panel de control de comentarios
@endsection

@section('content')
<div class="row" id="comments">
    @include('comments.modal')
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Comentarios frecuentes</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:comentariosfrecuentes"])
            </div>
            <div class="box-body">
                <div class="col-sm-2" style="float: right;">
                    <button v-on:click="addNewComment('{{route('comments_store')}}')" class="btn btn-block btn-success btn-sm"><span class="fa fa-fw fa-plus-circle"></span> Nuevo comentario</button>
                </div>
                <br>
                <br>
                <form action="{{ route('comments') }}" style="float: right;">
                    <input type="hidden" id="ifilter" name="ifilter">
                    <div class="btn-group" role="group" aria-label="Basic example">
                        @switch($iFilter)
                            @case(1)
                            <button onclick="filter(1)" type="submit" class="btn btn-secondary active">Activos</button>
                            <button onclick="filter(2)" type="submit" class="btn btn-secondary">Inactivos</button>
                            <button onclick="filter(3)" type="submit" class="btn btn-secondary">Todos</button>
                            @break
                            @case(2)
                            <button onclick="filter(1)" type="submit" class="btn btn-secondary">Activos</button>
                            <button onclick="filter(2)" type="submit" class="btn btn-secondary active">Inactivos</button>
                            <button onclick="filter(3)" type="submit" class="btn btn-secondary">Todos</button>
                            @break
                            @case(3)
                            <button onclick="filter(1)" type="submit" class="btn btn-secondary">Activos</button>
                            <button onclick="filter(2)" type="submit" class="btn btn-secondary">Inactivos</button>
                            <button onclick="filter(3)" type="submit" class="btn btn-secondary active">Todos</button>
                            @break
                        @endswitch
                    </div>
                </form>
                <br>
                <br>
                <table id="commentsTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Comentario</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th>Editado por</th>
                            <th>-</th>
                            <th>-</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="comment in lComments">
                            <td>@{{comment.comment}}</td>
                            <td>@{{comment.is_delete ? 'Inactivo' : 'Activo'}}</td>
                            <td>@{{comment.user_created.name}}</td>
                            <td>@{{comment.user_edited.name}}</td>
                            <td><button class="btn btn-warning" v-on:click="editComment(comment.id, comment.comment, '{{route('comments_update', ':id')}}')"><span class="glyphicon glyphicon-pencil"></span></button></td>
                            <td>
                                <button v-if="comment.is_delete == 0" class="btn btn-danger" v-on:click="deleteComment(comment.id, comment.comment,'{{route('comments_destroy', ':id')}}')"><span class="glyphicon glyphicon-trash"></span></button>
                                <button v-if="comment.is_delete == 1" class="btn btn-info" v-on:click="recoverComment(comment.id, comment.comment,'{{route('comments_recover', ':id')}}')"><span class="glyphicon glyphicon-open"></span></button>
                            </td>
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
<script src="{{asset("assets/pages/scripts/filter.js")}}" type="text/javascript"></script>
<script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
<script>
    function ServerData () {
        this.lComments = <?php echo json_encode($lComments) ?>;
    }
    
    var oServerData = new ServerData();
    var oGui = new SGui();
</script>
<script src="{{ asset("assets/pages/scripts/comments/comments.js") }}" type="text/javascript"></script>
<script>
    function reloadTable() {
        let table = $('#commentsTable').DataTable({
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
@endsection