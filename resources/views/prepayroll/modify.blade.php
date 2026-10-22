@extends("theme.$theme.layout")
@section('title')
Checadas
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
            "order": [[ 1, 'desc' ]],
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
    </script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Modificar cierre de prenómina</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:cierrepre"])
                
                <div class="row">
                    <div class="col-md-5 col-md-offset-7">
                        <div class="row">
                            <div class="col-md-4 col-md-offset-8">
                                <a href="{{route('crear_modificarcierreprenomina')}}" class="btn btn-block btn-success btn-sm">
                                    <i class="fa fa-fw fa-plus-circle"></i> Nuevo
                                </a>
                            </div>
                        </div>
                        <br>
                    
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Tipo nómina</th>
                            <th>Año</th>
                            <th>Num nómina</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Desactiva cierre</th>
                            <th>Días prorroga </th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modify as $mod)
                        <tr>
                            <td>{{$mod->week == 1 ? 'Semanal' : 'Quincenal' }}</td>
                            <td>{{$mod->year}}</td>
                            <td>{{$mod->num}}</td>
                            <td>{{$mod->week == 1 ? $mod->ini : 'NA'}}</td>
                            <td>{{$mod->fin}}</td>
                            <td>{{$mod->applies == 0 ? 'No' : 'Sí' }}</td>
                            <td>{{$mod->day}}</td>
                            <td>
                                <a href="{{route('editar_modificarcierreprenomina', ['id' => $mod->idPayroll])}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                <a href="{{route('eliminar_modificarcierreprenomina', ['id' => $mod->idPayroll])}}" class="btn-accion-tabla tooltipsC" title="Eliminar este registro">
                                    <i class="fa fa-fw fa-trash text-danger"></i>
                                </a>
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