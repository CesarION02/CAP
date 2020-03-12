@extends("theme.$theme.layout")
@section('title')
Plantilla Horarios
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/funciones.js")}}" type="text/javascript"></script>
<script>
    $(document).ready( function () {
    $('#myTable').DataTable();
    } );

</script>

@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Plantilla Horarios</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('programar',$dgroup)}}" class="btn btn-block btn-success btn-sm">
                        <i class="fa fa-fw fa-plus-circle"></i> Asignar horario
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                            @foreach ($assigns as $assign)
                            <tr>
                                <td>{{$assign->name}}</td>
                                <td>{{$assign->startDate}}</td>
                                <td>@if($assign->endDate == null)
                                        N/A
                                    @else
                                        {{$assign->endDate}}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{route('editar_programacion', ['id' => $assign->idAssign, 'dgroup' => $dgroup])}}" class="btn-accion-tabla tooltipsC" title="Ver/Editar este registro">
                                        <i class="fa fa-fw fa-pencil"></i>
                                    </a>
                                    <form action="{{route('eliminar', ['id' => $assign->idAssign])}}" class="d-inline form-eliminar" method="POST">
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