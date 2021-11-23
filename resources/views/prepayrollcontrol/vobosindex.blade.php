@extends("theme.$theme.layout")
@section('title')
    VoBo de prenóminas
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
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
            "order": [[ 2, 'asc' ]],
            "scrollX": true,
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
                <h3 class="box-title">Prenóminas VoBos</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php"])
                <div class="row">
                    <div class="col-md-8 col-md-offset-4">
                        <div class="row">
                            <div class="col-md-3 col-md-offset-1">
                                <select name="pay-type" id="pay-type" class="form-control">
                                    <option value="1">Semana</option>
                                    <option value="2">Quincena</option>
                                    <option value="3" selected>Todos</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <form action="{{ route('vobos') }}">
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
            </div>
            <div class="box-body">
                <table class="table table-condensed" style="width:100%" id="myTable">
                    <thead>
                        <tr>
                            <th title="Número de semana"># Sem</th>
                            <th title="Número de quincena"># Qui</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th title="Usuario visto bueno">Usr VoBo</th>
                            <th title="Requerido">Req</th>
                            <th title="Visto bueno">VoBo</th>
                            <th title="Fecha visto bueno">Fecha</th>
                            <th>Rechazado</th>
                            <th title="Fecha rechazo">Fecha Rech.</th>
                            <th>-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lControls as $oCtrl)
                            <tr style="background-color: {{ $oCtrl->is_vobo ? ("rgb(203, 255, 202)") : ($oCtrl->is_rejected ? "rgb(194, 126, 140)" : "") }}">
                                <td>{{ $oCtrl->num_week }}</td>
                                <td>{{ $oCtrl->num_biweek }}</td>
                                <td style="white-space: nowrap;">{{ $oCtrl->num_week > 0 ? $oCtrl->ini : \Carbon\Carbon::parse($oCtrl->dt_cut)->subDays(14)->toDateString() }}</td>
                                <td style="white-space: nowrap;">{{ $oCtrl->num_week > 0 ? $oCtrl->fin : $oCtrl->dt_cut }}</td>
                                <td>{{ $oCtrl->name }}</td>
                                <td>{{ $oCtrl->is_required ? "SÍ" : "NO" }}</td>
                                <td>{{ $oCtrl->is_vobo ? "SÍ" : "NO" }}</td>
                                <td>{{ $oCtrl->dt_vobo }}</td>
                                <td>{{ $oCtrl->is_rejected ? "RECHAZADO" : "-" }}</td>
                                <td>{{ $oCtrl->dt_rejected }}</td>
                                <td>
                                    @if(\Auth::user()->id == $oCtrl->user_vobo_id)
                                        @if(! $oCtrl->is_vobo)
                                            <form action="{{ route('dar_vobo', $oCtrl->id_control) }}" method="POST">
                                                @csrf
                                                <button title="Visto bueno" type="submit"><i class="fa fa-check" aria-hidden="true"></i></button>
                                            </form>
                                        @endif
                                        @if(! $oCtrl->is_rejected)
                                            <form action="{{ route('rechazar_vobo', $oCtrl->id_control) }}" method="POST">
                                                @csrf
                                                <button title="Rechazar" type="submit"><i class="fa fa-ban" aria-hidden="true"></i></button>
                                            </form>
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