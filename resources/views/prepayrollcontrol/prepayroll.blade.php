@extends("theme.$theme.layout")
@section('title')
Registros fuera de tiempo
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    
    
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
	
	
	
	
	
    
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
                @if ($week == 1)
                    <h3 class="box-title">Semana {{$binnacle[0]->num}} del {{$binnacle[0]->ini}} al {{$binnacle[0]->fin}}</h3>
                @else
                <h3 class="box-title">Quincena {{$binnacle[0]->num}} del {{$binnacle[0]->ini}} al {{$binnacle[0]->fin}}</h3>
                @endif
                <div class="box-tools pull-right">
                    @if ($week == 1)
                    <a href="{{route('bitacora_reg_s')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                    @else
                    <a href="{{route('bitacora_reg_q')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                    @endif
                    <a href="{{route('bitacora_reg_q')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Reprocesar
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Tipo registro</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Fecha de registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cambio as $cam)
                        <tr>
                            <td>Cambio de turno</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($cam->dateI)}}</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($cam->dateS)}}</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($cam->updated_at)}}</td>
                        </tr>
                        @endforeach
                        @foreach ($incidencias as $inc)
                        <tr>
                            <td>Incidencia</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($inc->start_date)}}</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($inc->end_date)}}</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($inc->updated_at)}}</td>
                        </tr>
                        @endforeach
                        @foreach ($checadas as $che)
                        <tr>
                            <td>Checadas manuales</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($che->date)}}</td>
                            <td>NA</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($che->updated_at)}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection