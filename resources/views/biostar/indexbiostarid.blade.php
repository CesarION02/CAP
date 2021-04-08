@extends("theme.$theme.layoutcustom")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
@endsection
@section('title')
    {{ 'Colaborador vs Biostar' }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Colaborador vs Biostar</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php"])
            </div>
            <div class="box-body" id="appEmpVsBiostar">
                <table class="table table-striped table-bordered table-hover" id="empb_table">
                    <thead>
                        <tr>
                            <th>ID CAP</th>
                            <th>Colaborador CAP</th>
                            <th>ID Biostar</th>
                            <th>Editar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="empRow in lVueEmployees">
                            <td>@{{ oVueGui.pad(empRow.id, 6) }}</td>
                            <td>@{{ empRow.name }}</td>
                            <td>
                                <input :id="empRow.id" type="number" v-model="empRow.biostar_id" :disabled="!empRow.actionEnabled" style="text-align: center;">
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
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('dt/jszip.min.js') }}"></script>
    <script src="{{ asset('dt/pdfmake.min.js') }}"></script>
    <script src="{{ asset('dt/vfs_fonts.js') }}"></script>
    <script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.print.min.js') }}"></script>
    <script src="{{ asset("js/excel/xlsx.full.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("js/excel/FileSaver.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>

    <script>
        $(document).ready( function () {
            $('#empb_table').DataTable({
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