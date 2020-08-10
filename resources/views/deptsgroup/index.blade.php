@extends("theme.$theme.layout")
@section('title')
    Grupos de departamentos CAP
@endsection

@section('content')
<div class="row" id="deptsGrpApp">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Grupos de departamentos CAP</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:grupodep"])
                <div class="box-tools pull-right">
                    <div class="row">
                        <div class="col-md-2 col-md-offset-10">
                            <a v-on:click="newGroupModal()" class="btn btn-block btn-success btn-sm">
                                <i class="fa fa-fw fa-plus-circle"></i> Nuevo Grupo
                            </a>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <form action="{{ route('depts_grp') }}">
                            <div class="col-md-3 col-md-offset-9">
                                <div class="input-group">
                                    <select v-model="vueData.iFilter" class="form-control" name="filter_acts">
                                        <option value="1" selected>Activos</option>
                                        <option value="2">Inactivos</option>
                                        <option value="3">Todos</option>
                                    </select>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit">
                                            <i class="glyphicon glyphicon-search"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table id="deptsGroupsTableId" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Departamentos asignados</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="group in vueData.lGroups">
                            <td>@{{ group.name }}</td>
                            <td>@{{ group.depts }}</td>
                            <td>
                                <button v-on:click="showGrpModal(group)" 
                                    class="btn-accion-tabla tooltipsC" title="Modificar departamentos">
                                    <i class="glyphicon glyphicon-list-alt"></i>
                                </button>
                                <button v-on:click="editGrpModal(group)" 
                                    class="btn-accion-tabla tooltipsC" title="Editar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </button>
                                <button v-show="! group.is_delete" v-on:click="prevDeleteGroup(group)" 
                                    class="btn-accion-tabla tooltipsC" title="Borrar este registro">
                                    <i class="glyphicon glyphicon-trash"></i>
                                </button>
                                <button v-show="group.is_delete" v-on:click="prevDeleteGroup(group)" 
                                    class="btn-accion-tabla tooltipsC" title="Activar este registro">
                                    <i class="glyphicon glyphicon-ok"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('deptsgroup.modal')
    @include('deptsgroup.editm')
</div>
@endsection

@section('scripts')
<script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
<script>
        function ServerData () {
            this.lGroups = <?php echo json_encode($lGroups) ?>;
            this.lDepts = <?php echo json_encode($lDepts) ?>;
            this.iFilter = <?php echo json_encode($iFilter) ?>;
        }
        
        var oServerData = new ServerData();
        var oGui = new SGui();
</script>
<script src="{{ asset("assets/pages/scripts/deptsgrp/SDepartment.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/pages/scripts/deptsgrp/VueCore.js") }}" type="text/javascript"></script>
<script>
    function reloadTable() {
            let table = $('#deptsGroupsTableId').DataTable({
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

            // setInterval( function () {
            //     table.ajax.reload();
            // }, 60000 );
    }

    reloadTable();
    
    
</script>
@endsection