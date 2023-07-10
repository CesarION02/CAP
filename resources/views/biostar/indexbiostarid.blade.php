@extends("theme.$theme.layoutcustom")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/nv/datatables.css") }}">
@endsection
@section('title')
    {{ 'Empleado vs Biostar' }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Empleados vs BioStar</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php"])
            </div>
            <div class="box-body" id="appEmpVsBiostar">
                <div class="row">
                    <div class="col-md-offset-10 col-md-2">
                        <select class="form-control" id="sel-biostar-opt">
                            <option value="0" selected>Todos</option>
                            <option value="1">Vinculados</option>
                            <option value="2">Faltantes</option>
                        </select>
                    </div>
                </div>
                <table class="table table-striped table-bordered table-hover" id="empb_table">
                    <thead>
                        <tr>
                            <th>ID CAP</th>
                            <th>Empleado CAP</th>
                            <th>Num colab.</th>
                            <th>Depto GH</th>
                            <th>ID BioStar</th>
                            <th>Editar</th>
                            <th>extId</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="empRow in lVueEmployees">
                            <td>@{{ oVueGui.pad(empRow.id, 6) }}</td>
                            <td>@{{ empRow.name.toUpperCase() }}</td>
                            <td style="text-align: center;">@{{ empRow.num_employee }}</td>
                            <td>@{{ empRow.depto_gh }}</td>
                            <td>
                                <input :id="empRow.id" type="number" v-model="empRow.biostar_id" :disabled="!empRow.actionEnabled" style="text-align: center; width: 100px;">
                            </td>
                            <td>
                                <button v-on:click="editBiostarId(empRow)"
                                        class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </button>
                                <button v-if="empRow.actionEnabled" v-on:click="updateBiostarId(empRow)" 
                                        class="btn-accion-tabla tooltipsC" title="Actualizar">
                                    <i class="glyphicon glyphicon-ok"></i>
                                </button>
                            </td>
                            <td>@{{ empRow.biostar_id }}</td>
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
    <script src="{{ asset("dt/nv/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset("js/excel/xlsx.full.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("js/excel/FileSaver.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>

    <script>
        $(document).ready( function () {
            $.fn.dataTable.ext.search.push(
                function( settings, data, dataIndex ) {
                    // var min = parseInt( $('#min').val(), 10 );
                    let biostarOpt = parseInt( $('#sel-biostar-opt').val(), 10 );
                    let externalId = data[6];

                    switch (biostarOpt) {
                        case 0:
                            return true;

                        case 1:
                            return parseInt( externalId ) > 0;

                        case 2:
                            return externalId == "" || parseInt( externalId ) == 0;

                        default:
                            break;
                    }

                    return false;
                }
            );

            var oTable = $('#empb_table').DataTable({
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
                columnDefs: [
                    {
                        targets: [ 6 ],
                        visible: false
                    },
                ],
                "dom": 'Bfrtip',
                "lengthMenu": [
                    [ 10, 25, 50, 100, -1 ],
                    [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                ],
                "buttons": [
                    'pageLength',
                    { extend: 'copy', text: 'Copiar'}, 'csv', 'excel', { extend: 'print', text: 'Imprimir'}
                    ]
            });

            $('#sel-biostar-opt').change( function() {
                oTable.draw();
            });
        });

    </script>

    <script>
        function GlobalData () {
            this.lEmployees = <?php echo json_encode($lEmployees) ?>;
        }
        
        var oData = new GlobalData();
        var oGui = new SGui();
    </script>

    <script src="{{asset("assets/pages/scripts/biostar/VueEmpVsBiostar.js")}}" type="text/javascript"></script>
@endsection