@extends("theme.$theme.layout")
@section('title')
Asignar plantilla
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/funciones.js")}}" type="text/javascript"></script>
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
                "colReorder": true,
                "dom": 'Bfrtip',
                "lengthMenu": [
                    [ 10, 25, 50, 100, -1 ],
                    [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                ],
                "buttons": [
                        'copy', 'csv', 'excel', 'print'
                    ]
            });
        });

    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    });
</script>

@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Asignar plantilla horarios</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:asignacionhorario"])
                <div class="box-tools pull-right">
                    <a href="{{route('crear_asignacion','1')}}" class="btn btn-block btn-success btn-sm">
                        <i class="fa fa-fw fa-plus-circle"></i> Asignar por empleado
                    </a>
                    <a href="{{route('crear_asignacion','2')}}" class="btn btn-block btn-success btn-sm">
                            <i class="fa fa-fw fa-plus-circle"></i> Asignar por departamento
                        </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Departamento CAP</th>
                            <th>Empleado</th>
                            <th>Horario</th>
                            <th>Grupo</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                            @foreach ($datas as $data)
                            <tr>
                                <td>@if($data->department_id == null)
                                        N/A
                                    @else
                                        {{$data->department->name}}
                                    @endif
                                </td>
                                <td>@if($data->employee_id == null)
                                        N/A
                                    @else
                                    {{$data->employee->name}}
                                    @endif
                                    </td>
                                <td>{{$data->schedule->name}}</td>
                                <td>@if($data->group_assign_id == null)
                                        N/A
                                    @else
                                        {{$data->group_assign_id}}
                                    @endif
                                </td>
                                <td>@if($data->start_date == null)
                                        N/A
                                    @else
                                        {{$data->start_date}}
                                    @endif
                                </td>
                                <td>@if($data->end_date == null)
                                        N/A
                                    @else
                                        {{$data->end_date}}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{route('editar_asignacion', ['id' => $data->id])}}" class="btn-accion-tabla tooltipsC" title="Editar este registro">
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