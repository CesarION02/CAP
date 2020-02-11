@extends("theme.$theme.layout")
@section('title')
Turnos
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
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
                <h3 class="box-title">Turnos</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('crear_turno')}}" class="btn btn-block btn-success btn-sm">
                        <i class="fa fa-fw fa-plus-circle"></i> Nuevo registro
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Horas Trabajar</th>
                            <th>Tiempo extra</th>
                            <th>Orden</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $data)
                        <tr>
                            <td>{{$data->name}}</td>
                            <td>{{$data->entry}}</td>
                            <td>{{$data->departure}}</td>
                            <td>{{$data->work_time}}</td>
                            <td>{{$data->overtimepershift}}</td>
                            <td>{{$data->order}}</td>
                            <td>
                                <a href="{{route('editar_turno', ['id' => $data->id])}}" class="btn-accion-tabla tooltipsC" title="Editar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                <form action="{{route('eliminar_turno', ['id' => $data->id])}}" class="d-inline form-eliminar" method="POST">
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