@extends("theme.$theme.layout")
@section('title')
Asignar plantilla
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.print.min.js') }}"></script>
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

@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Asignar horario global</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:asignacionhorario"])
                <div class="box-tools pull-right">
                    <a href="{{route('crear_asignacion','1')}}" class="btn btn-block btn btn-info btn-sm">
                        <i class="fa fa-fw fa-plus-circle"></i> Asignar por empleado
                    </a>
                    <a href="{{route('crear_asignacion','2')}}" class="btn btn-block btn btn-info btn-sm">
                            <i class="fa fa-fw fa-plus-circle"></i> Asignar por depto. CAP
                        </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Grupo empleados</th>
                            <th>Departamento CAP</th>
                            <th>Fecha inicial</th>
                            <th>Fecha final</th>
                            <th>Horario</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                            @foreach ($datas as $data)
                            <tr>
                                <td>
                                    {{$data->nombreEmpleado}}
                                </td>
                                <td>@if($data->group_assign_id == null)
                                        N/A
                                    @else
                                        {{$data->group_assign_id}}
                                    @endif
                                </td>
                                <td>
                                    N/A
                                </td>
                                
                                <td>@if($data->fecha_inicio == null)
                                        N/A
                                    @else
                                        {{\App\SUtils\SDateTimeUtils::orderDate($data->fecha_inicio)}}
                                    @endif
                                </td>
                                <td>@if($data->fecha_fin == null)
                                        N/A
                                    @else
                                        {{\App\SUtils\SDateTimeUtils::orderDate($data->fecha_fin)}}
                                    @endif
                                </td>
                                <td>{{$data->nombreHorario}}</td>
                                <td>
                                    <a href="{{route('editar_asignacion', ['id' => $data->id])}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                        <i class="fa fa-fw fa-pencil"></i>
                                    </a>
                                    <form action="{{route('eliminar_asignacion', ['id' => $data->id])}}" class="d-inline form-eliminar" method="POST">
                                        @csrf @method("delete")
                                        <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Eliminar este registro">
                                            <i class="fa fa-fw fa-trash text-danger"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @foreach ($datasD as $data)
                            <tr>
                                <td>
                                    N/A
                                </td>
                                <td>@if($data->group_assign_id == null)
                                        N/A
                                    @else
                                        {{$data->group_assign_id}}
                                    @endif
                                </td>
                                <td>
                                    {{$data->nombreEmpleado}}
                                </td>
                                <td>{{$data->nombreHorario}}</td>
                                <td>@if($data->fecha_inicio == null)
                                        N/A
                                    @else
                                        {{\App\SUtils\SDateTimeUtils::orderDate($data->fecha_inicio)}}
                                    @endif
                                </td>
                                <td>@if($data->fecha_fin == null)
                                        N/A
                                    @else
                                        {{\App\SUtils\SDateTimeUtils::orderDate($data->fecha_fin)}}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{route('editar_asignacion', ['id' => $data->id])}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                        <i class="fa fa-fw fa-pencil"></i>
                                    </a>
                                    <form action="{{route('eliminar_asignacion', ['id' => $data->id])}}" class="d-inline form-eliminar" method="POST">
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