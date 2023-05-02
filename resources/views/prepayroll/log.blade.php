@extends("theme.$theme.layout")
@section('title')
    Controles de ajustes
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
    <style>
        .th {
            font-size: 10pt;
        }
        .tr {
            font-size: 5pt;
        }
    </style>
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    
    
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
	
	
	
	
	
    
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/prepayroll/SAdjustsAuth.js") }}" type="text/javascript"></script>
<script>
    $(document).ready( function () {
        $.fn.dataTable.moment('DD/MM/YYYY');
        $('#adjusts_table').DataTable({
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
    moment.locale('es');

    $(function() {
        let start = moment(<?php echo json_encode($startDate) ?>);
        let end = moment(<?php echo json_encode($endDate) ?>);

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
                <h3 class="box-title">Controles de ajustes</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php"])
                <div class="row">
                    <div class="col-md-5 col-md-offset-7">
                        <div class="row">
                            <div style="text-align: right" class="col-md-12">
                                <form action="{{ route('ajustes_log') }}">
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
                        </div>
                    </div>
            </div>
            <div class="box-body" id="adjustsApp">
                <table class="table table-striped table-bordered table-hover" id="adjusts_table">
                    <thead>
                        <tr 
                            style="font-size: 10pt;"
                        >
                            <th>Num</th>
                            <th>Empleado</th>
                            <th>Fecha</th>
                            <th>Ajuste</th>
                            <th>Minutos</th>
                            <th>Comentarios</th>
                            <th>Hecho por</th>
                            <th>Estatus</th>
                            <th>Por</th>
                            <th>-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lAdjs as $adj)
                            <tr 
                                style="font-size: 8pt;"
                            >
                                <td>{{ $adj->num_employee }}</td>
                                <td>{{ $adj->employee }}</td>
                                <td style="white-space: nowrap;">{{ $adj->dt_date }}</td>
                                <td>{{ $adj->type_name }}</td>
                                <td>{{ $adj->minutes > 0 ? $adj->minutes : '-' }}</td>
                                <td>{{ $adj->comments }}</td>
                                <td>{{ $adj->made_by }}</td>
                                <td>{{ $adj->is_authorized ? "APROBADO" : ( $adj->is_rejected ? "RECHAZADO" : "PENDIENTE" ) }}</td>
                                <td>{{ $adj->is_rejected ? $adj->rej_by : $adj->auth_by }}</td>
                                <td>
                                    @if ($adj->user_auth_id == \Auth::user()->id)
                                        @if (! $adj->is_authorized)
                                            <a href="{{ route('autorizar_ajuste', $adj->id_control) }}" class="btn-accion-tabla tooltipsC" title="Aprobar">
                                                <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        @if (! $adj->is_rejected)
                                            <a href="{{ route('rechazar_ajuste', $adj->id_control) }}" class="btn-accion-tabla tooltipsC" title="Rechazar">
                                                <i class="fa fa-thumbs-o-down" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection