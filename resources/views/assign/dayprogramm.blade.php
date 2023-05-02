@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection
@section('title')
Plantilla horarios
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/funciones.js")}}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    <script>
        function ServerData () {
                this.endDate = <?php echo json_encode($endDate); ?>;
                this.startDate = <?php echo json_encode($startDate); ?>;
            }
            
            var oServerData = new ServerData();
    </script>
    <script type="text/javascript">
        $(function() {

            var start = moment(oServerData.startDate);
            var end = moment(oServerData.endDate);

            function cb(start, end) {
                $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            }

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Último mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'A fin de año': [moment(), moment().endOf('year')]
                }
            }, cb);

            cb(start, end);
        });

        $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
            document.getElementById('start_date').value = picker.startDate.format('YYYY-MM-DD');
            document.getElementById('end_date').value = picker.endDate.format('YYYY-MM-DD');
        });
    </script>
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
            "order": [[ 1, 'desc' ]],
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
                ]
        });
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Asignar horario fijo</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:asignacionhorario"])
                <br>
                <br>
                <div class="box-tools pull-right">
                    <div class="col-md-6"></div>
                    <div class="col-md-4">
                        <form action="{{route('index_programacion_dia')}}">
                            <input type="hidden" name="start_date" id="start_date">
                            <input type="hidden" name="end_date" id="end_date">
                            <div class="input-group">
                                <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
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
                    <div class="col-md-2">
                        <a href="{{route('programar_dia',$dgroup)}}" class="btn btn-block btn-info btn-sm">
                            <i class="fa fa-fw fa-plus-circle"></i> Asignar horario
                        </a>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Fecha inicial</th>
                            <th>Horario</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                            @foreach ($assigns as $assign)
                            <tr>
                                <td>{{$assign->nombreEmpleado}}</td>
                                <td>@if($assign->fecha_inicio == null)
                                    N/A
                                    @else
                                    {{\App\SUtils\SDateTimeUtils::orderDate($assign->fecha_inicio)}}
                                    @endif
                                </td>
                                <td>{{$assign->nombreHorario}}</td>
                                <td>
                                    <a href="{{route('editar_programacion', ['id' => $assign->id])}}" class="btn-accion-tabla tooltipsC" title="Ver/Modificar este registro">
                                        <i class="fa fa-fw fa-pencil"></i>
                                    </a>
                                    <form action="{{route('eliminar', ['id' => $assign->id])}}" class="d-inline form-eliminar" method="POST">
                                        @csrf @method("delete")
                                        <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Eliminar este registro">
                                            <i class="fa fa-fw fa-trash text-danger"></i>
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