@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
@endsection
@section('title')
{{ $sTitle }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte de la STPS</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body" id="reportDelayApp">
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Empleado</th>
                                    {{-- <th>Fecha entrada</th> --}}
                                    <th>Hora_entrada</th>
                                    {{-- <th>Fecha salida</th> --}}
                                    <th>Hora_salida</th>
                                    {{-- <th v-if="oData.tReport == oData.REP_DELAY">Retardo (min)</th>
                                    <th v-else>Horas Extra</th> --}}
                                    <th>Horas Extra Dobles</th>
                                    {{-- <th v-if="oData.tReport == oData.REP_HR_EX">Hr_progr_Sal</th> --}}
                                    <th>Horas Extra Triples</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in oData.lRows" :class="getCssClass(row.delayMins, oData.tReport)">
                                    <td>@{{ vueGui.pad(row.numEmployee, 6) }}</td>
                                    <td>@{{ row.employee }}</td>
                                    {{-- <td>@{{ row.inDate }}</td> --}}
                                    <td>@{{ row.inDateTime }}</td>
                                    {{-- <td>@{{ row.outDate }}</td> --}}
                                    <td>@{{ row.outDateTime }}</td>
                                    {{-- <td v-if="oData.tReport == oData.REP_DELAY">@{{ row.delayMins }}</td>
                                    <td v-else>@{{ row.extraHours }}</td> --}}
                                    <td>@{{ row.extraDouble }}</td>
                                    <td>@{{ row.extraTriple }}</td>
                                    {{-- <td v-if="oData.tReport == oData.REP_HR_EX">@{{ row.outDateTimeSch }}</td> --}}
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
            this.REP_HR_EX = <?php echo json_encode(\SCons::REP_HR_EX) ?>;
            this.REP_DELAY = <?php echo json_encode(\SCons::REP_DELAY) ?>;

            // this.minsCol = this.tReport == this.REP_DELAY ? 4 : 4;
        }
        
        var oData = new GlobalData();
        var oGui = new SGui();

        /**
         * Convierte los minutos en entero a formato 00:00
         *
         * @param int time
         * 
         * @return string 00:00
         */
        function convertToHoursMins(time) 
        {
            if (time < 1) {
                return "00:00";
            }

            let hours = Math.floor(time / 60);          
            let minutes = time % 60;

            return "" + hours + ":" + minutes;
        }
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
                order: [[0, 'asc']],
                "columnDefs": [
                    {
                        "targets": [ oData.hiddenCol ],
                        "visible": false
                    }
                ],
                
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
                            extend: 'excel',
                            exportOptions: {
                                columns: oData.toExport
                            }
                        },
                        {
                            extend: 'copy',
                            exportOptions: {
                                columns: oData.toExport
                            }
                        },
                        {
                            extend: 'csv',
                            exportOptions: {
                                columns: oData.toExport
                            }
                        },
                        {
                            extend: 'print',
                            exportOptions: {
                                columns: oData.toExport
                            }
                        }
                    ]
            });
        });
    </script>
@endsection