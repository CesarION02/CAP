@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
@endsection
@section('title')
Reporte de retardos
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte de retardos</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body" id="reportDelayApp">
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Empleado</th>
                                    <th>Fecha entrada</th>
                                    <th>Hora entrada</th>
                                    <th>Fecha salida</th>
                                    <th>Hora salida</th>
                                    <th>Retardo (min)</th>
                                    <th>Comentarios</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in oData.lRows">
                                    <td>@{{ vueGui.pad(row.numEmployee, 6) }}</td>
                                    <td>@{{ row.employee }}</td>
                                    <td>@{{ row.inDate }}</td>
                                    <td>@{{ row.inDateTime }}</td>
                                    <td>@{{ row.outDate }}</td>
                                    <td>@{{ row.outDateTime }}</td>
                                    <td>@{{ row.delayMins }}</td>
                                    <td>@{{ row.comments }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.print.min.js') }}"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    
    <script>
        function GlobalData () {
            this.lRows = <?php echo json_encode($lRows) ?>;
        }
        
        var oData = new GlobalData();
        var oGui = new SGui();
    </script>

    <script src="{{asset("assets/pages/scripts/report/delayReport.js")}}" type="text/javascript"></script>

    <script>
        $(document).ready(function() {
            $('#delays_table').DataTable({
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
                        'pageLength', 'copy', 'csv', 'excel', 'print'
                    ]
            });
        });
    </script>
@endsection