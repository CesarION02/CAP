@extends("theme.$theme.layout")
@section('title')
Periodos procesados
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.print.min.js') }}"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
<script>
    $(document).ready( function () {
        $.fn.dataTable.moment('DD/MM/YYYY');
        $('#myTable').DataTable({
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
            "order": [[ 0, 'desc' ], [ 1, 'desc' ], [2, 'asc']],
            "colReorder": true,
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
                    'csv', 
                    'excel', 
                    {
                        extend: 'print',
                        text: 'Imprimir'
                    }
                ],
        });

    });


</script>
<script>
    $(function() {
        var start = moment(<?php echo json_encode($start_date) ?>);
        var end = moment(<?php echo json_encode($end_date) ?>);

        function cb(start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            document.getElementById("start-date").value = start.format('YYYY-MM-DD');
            document.getElementById("end-date").value = end.format('YYYY-MM-DD');
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Este año': [moment().startOf('year'), moment().endOf('year')],
                'Año pasado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        }, cb);

        cb(start, end);
    });

    $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        document.getElementById("start-date").value = picker.startDate.format('YYYY-MM-DD');
        document.getElementById("end-date").value = picker.endDate.format('YYYY-MM-DD');
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Periodos procesados</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:periodos"])
                <div class="row">
                    <div class="col-md-8 col-md-offset-4">
                        <div class="row">
                            <div class="col-md-9">
                                <form action="{{ route('periodo') }}">
                                    <input type="hidden" id="start-date" name="start_date">
                                    <input type="hidden" id="end-date" name="end_date">
                                    <div class="input-group">
                                        <div id="reportrange" 
                                            style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                            <i class="fa fa-calendar"></i>&nbsp;
                                            <span></span> <i class="fa fa-caret-down"></i>
                                        </div>
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="submit">
                                                <i class="glyphicon glyphicon-search"></i>
                                            </button>
                                        </span>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <a href="{{route('crear_periodo')}}" class="btn btn-block btn-success btn-sm">
                                    <i class="fa fa-fw fa-plus-circle"></i> Procesar
                                </a>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Año</th>
                            <th>Número semana</th>
                            <th>Número quincena</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Fecha corte</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataw as $data)
                        <tr>
                            <td>{{$data->year}}</td>
                            <td>{{$data->num}}</td>
                            <td>--</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($data->ini)}}</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($data->fin)}}</td>
                            <td>--</td>
                           <td>
                                <form action="{{route('eliminar_festivo', ['id' => $data->id])}}" class="d-inline form-eliminar" method="POST">
                                    @csrf @method("delete")
                                    <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Eliminar este registro">
                                        <i class="fa fa-fw fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @foreach ($datab as $data)
                        <tr>
                            <td>{{$data->year}}</td>
                            <td>--</td>
                            <td>{{$data->num}}</td>
                            <td>--</td>
                            <td>--</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($data->dt_cut)}}</td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection