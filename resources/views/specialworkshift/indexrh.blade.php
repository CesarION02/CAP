@extends("theme.$theme.layout")
@section('title')
Turno especial
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
<script>
    $(document).ready( function () {
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
           
            "lengthMenu": [
                [ 10, 25, 50, 100, -1 ],
                [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
            ],
            "buttons": [
                { extend: 'copy', text: 'Copiar'}, 'csv', 'excel', { extend: 'print', text: 'Imprimir'}
                ]
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
            showDropdowns: true,
            ranges: {
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
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

    function setFilterApprvd(filterType) {
        document.getElementById("filter-apprvd").value = filterType;
    }

    function setActiveClass(filterType) {
        let ft = filterType + "";
        switch (ft) {
            case "1":
                var element = document.getElementById("btnAppvd");
                element.classList.add("active");
                break;
            case "2":
                var element = document.getElementById("btnAppvdPen");
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

    function disableRangePicker(filterType) {
        
    }

    setActiveClass(<?php echo json_encode($filterType) ?>);
    disableRangePicker(<?php echo json_encode($filterType) ?>);
</script>

@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Cambio turnos</h3>
                <form action="{{ route('turno_especial_rh') }}">
                    <div class="row">
                        <div class="col-md-4 col-md-offset-3">
                            <input type="hidden" name="filter_apprvd" id="filter-apprvd" value="{{ $filterType }}">
                            <div class="btn-group" role="group" aria-label="...">
                                <button type="submit" id="btnAppvd" onclick="setFilterApprvd('1')" class="btn btn-default">Aprobados</button>
                                <button type="submit" id="btnAppvdPen" onclick="setFilterApprvd('2')" class="btn btn-default">Por aprobar</button>
                                <button type="submit" id="btnAll" onclick="setFilterApprvd('0')" class="btn btn-default">Todos</button>
                            </div>
                        </div>
                        <div class="col-md-5">
                                <input type="hidden" id="start-date" name="start_date">
                                <input type="hidden" id="end-date" name="end_date">
                                <div class="input-group" id="filter">
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
                        </div>
                    </div>
                </form>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Turno</th>
                            <th>Aprobado</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $data)
                        <tr>
                            <td>{{$data->nameEmp}}</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($data->datei)}}</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($data->dates)}}</td>
                            <td>{{$data->nameWork}}</td>
                            <td>{{$data->is_approved ? "SÍ" : "NO"}}</td>
                            <td>
                                <form action="{{ route('aprobar_turno_especial', ['id' => $data->id]) }}" class="d-inline" method="PUT">
                                    <button type="submit" class="btn-accion-tabla" title="Aprobar/Desaprobar turno especial">
                                        <i class="{{ $data->is_approved ? 'glyphicon glyphicon-ban-circle' : 'glyphicon glyphicon-ok' }}"></i>
                                    </button>
                                </form>
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