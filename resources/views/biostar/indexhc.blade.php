@extends("theme.$theme.layoutcustom")
@section('styles1')
<link rel="stylesheet" href="{{ asset("dt/nv/datatables.css") }}">
@endsection
@section('title')
    {{ 'Huellas y Rostros' }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Huellas y Rostros</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php"])
                <div class="row">
                    <form action="{{ route('biostar_users_index') }}">
                        <div class="col-md-3 col-md-offset-9">
                            
                           
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body" id="appHyR">
                <table class="table table-striped table-bordered table-hover" id="h_r_table">
                    <thead>
                        <tr>
                            <th>ID BioStar</th>
                            <th>Num Emp.</th>
                            <th>Empleado BioStar</th>
                            <th>Rostro</th>
                            <th>Huella</th>
                            <th>Departamento</th>
                            <th>Activo</th>
                            <th>Eliminado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="userRow in lVueUsers">
                            <td>@{{ vueGui.pad(userRow.id_user, 6) }}</td>
                            <td>@{{ vueGui.pad(userRow.num_employee, 6) }}</td>
                            <td>@{{ userRow.user_name }}</td>
                            <td>@{{ userRow.has_face ? 'SÍ' : 'NO' }}</td>
                            <td>@{{ userRow.has_fingerprint ? 'SÍ' : 'NO' }}</td>
                            <td>@{{ userRow.dept_name }}</td>
                            <td>@{{ userRow.is_active ? 'SÍ' : 'NO' }}</td>
                            <td>@{{ userRow.is_delete ? 'SÍ' : 'NO' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("dt/nv/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset("js/excel/xlsx.full.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("js/excel/FileSaver.min.js") }}" type="text/javascript"></script>

    <script>
        $(document).ready( function () {
            $('#h_r_table').DataTable({
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
            this.lUsers = <?php echo json_encode($lUsers) ?>;
        }
        
        var oData = new GlobalData();
    </script>

    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>

    <script>
        var oGui = new SGui();
        oGui.showLoading(5000);
    </script>

    <script src="{{asset("assets/pages/scripts/biostar/VueHyR.js")}}" type="text/javascript"></script>
@endsection