@extends("theme.$theme.layout")
@section('title')
    Empresas
@endsection

@section('content')
<div class="row" id="companiesApp">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Empresas</h3>
                <div class="box-tools pull-right">
                    <div class="row">
                        <div class="col-md-2 col-md-offset-10">
                            <button class="btn btn-block btn-success btn-sm" v-on:click="createCompany()">
                                <i class="fa fa-fw fa-plus-circle"></i> Nuevo registro
                            </button>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <form action="{{ route('company') }}">
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
                <table id="companiesTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>RFC</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="company in vueData.lCompanies">
                            <td>
                                @{{ company.name }}
                            </td>
                            <td>
                                @{{ company.fiscal_id }}
                            </td>
                            <td>
                                <button v-on:click="editCompany(company)" 
                                    class="btn-accion-tabla tooltipsC" title="Editar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </button>
                                <button v-show="! company.is_delete" v-on:click="prevDeleteCompany(company)" 
                                    class="btn-accion-tabla tooltipsC" title="Borrar este registro">
                                    <i class="glyphicon glyphicon-trash"></i>
                                </button>
                                <button v-show="company.is_delete" v-on:click="prevDeleteCompany(company)" 
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
    @include('companies.modalcompany')
</div>
@endsection

@section("scripts")
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script>
            function ServerData () {
                this.lCompanies = <?php echo json_encode($lCompanies) ?>;
                this.iFilter = <?php echo json_encode($iFilter) ?>;
            }
            
            var oServerData = new ServerData();
            var oGui = new SGui();
    </script>
    <script src="{{ asset("assets/pages/scripts/companies/SCompany.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/companies/SVueCompany.js") }}" type="text/javascript"></script>
    <script>
        function reloadTable() {
                let table = $('#companiesTable').DataTable({
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
                    "lengthMenu": [
                        [ 10, 25, 50, 100, -1 ],
                        [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                    ]
                });

                // setInterval( function () {
                //     table.ajax.reload();
                // }, 60000 );
        }

        reloadTable();
    
    </script>
@endsection