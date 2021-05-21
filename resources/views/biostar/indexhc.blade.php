@extends("theme.$theme.layoutcustom")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
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
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php"])
                <div class="row">
                    <form action="{{ route('biostar_users_index') }}">
                        <div class="col-md-3 col-md-offset-9">
                            <input type="hidden" name="filter_users" id="filter-users" value="{{ $filterType }}">
                            <div class="btn-group" role="group" aria-label="...">
                                <button type="submit" id="btnSin" onclick="setFilterUsers('1')" class="btn btn-default">Faltantes</button>
                                <button type="submit" id="btnAll" onclick="setFilterUsers('0')" class="btn btn-default">Todos</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body" id="appHyR">
                <table class="table table-striped table-bordered table-hover" id="h_r_table">
                    <thead>
                        <tr>
                            <th>ID BioStar</th>
                            <th>Colaborador BioStar</th>
                            <th>Huella</th>
                            <th>Rostro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="userRow in lVueUsers">
                            <td>@{{ userRow.id_user }}</td>
                            <td>@{{ userRow.user_name }}</td>
                            <td>@{{ userRow.has_fingerprint ? 'SÍ' : 'NO' }}</td>
                            <td>@{{ userRow.has_face ? 'SÍ' : 'NO' }}</td>
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
        function setFilterUsers(filterType) {
            document.getElementById("filter-users").value = filterType;
        }

        function setActiveClass(filterType) {
            let ft = filterType + "";
            switch (ft) {
                case "1":
                    var element = document.getElementById("btnSin");
                    element.classList.add("active");
                    break;
                case "0":
                    var element = document.getElementById("btnAll");
                    element.classList.add("active");
                    break;
            
                default:
                    break;
            }
        }

        let filterType = <?php echo json_encode($filterType) ?>;
        setActiveClass(filterType);
    </script>

    <script>
        function GlobalData () {
            this.lUsers = <?php echo json_encode($lUsers) ?>;
        }
        
        var oData = new GlobalData();
    </script>

    <script src="{{asset("assets/pages/scripts/biostar/VueHyR.js")}}" type="text/javascript"></script>
@endsection