@extends("theme.$theme.layout")
@section('title')
Asignar Día Festivo
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/funciones.js")}}" type="text/javascript"></script>
<script>
    $(document).ready( function () {
    $('#myTable').DataTable();
    } );

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
                <h3 class="box-title">Asignar Día Festivo</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('crear_asignacion_festivo','1')}}" class="btn btn-block btn-success btn-sm">
                        <i class="fa fa-fw fa-plus-circle"></i> Asignar por empleado
                    </a>
                    <a href="{{route('crear_asignacion_festivo','2')}}" class="btn btn-block btn-success btn-sm">
                        <i class="fa fa-fw fa-plus-circle"></i> Asignar por departamento
                    </a>
                    <a href="{{route('crear_asignacion_festivo','3')}}" class="btn btn-block btn-success btn-sm">
                            <i class="fa fa-fw fa-plus-circle"></i> Asignar por area
                        </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Departamento</th>
                            <th>Empleado</th>
                            <th>Día</th>
                            <th>Grupo</th>
                            <th>Fecha</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                            @foreach ($datas as $data)
                            <tr>
                                <td>@if($data->area_id == null)
                                        N/A
                                    @else
                                        {{$data->area->name}}
                                    @endif
                                </td>
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
                                <td>{{$data->holiday->name}}</td>
                                <td>@if($data->group_assign_id == null)
                                        N/A
                                    @else
                                        {{$data->group_assign_id}}
                                    @endif
                                </td>
                                <td>@if($data->date == null)
                                        N/A
                                    @else
                                        {{$data->date}}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{route('editar_asignacion_festivo', ['id' => $data->id])}}" class="btn-accion-tabla tooltipsC" title="Editar este registro">
                                        <i class="fa fa-fw fa-pencil"></i>
                                    </a>
                                    <form action="{{route('eliminar_asignacion_festivo', ['id' => $data->id])}}" class="d-inline form-eliminar" method="POST">
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