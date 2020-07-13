@extends("theme.$theme.layout")
@section('title')
Empleados
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
                <h3 class="box-title">Grupo departamentos - usuarios</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('crear_dgu')}}" class="btn btn-block btn-success btn-sm">
                        <i class="fa fa-fw fa-plus-circle"></i> Nuevo registro
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Nombre usuario</th>
                            <th>Grupos de departamentos</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0;?>
                        @foreach ($user as $g)

                        <tr>
                            <td>{{$g->nombreEmpleado}}</td>
                            <td>{{$departamentos[$i]}}</td>
                            <td>
                                <a href="{{route('editar_dgu', ['id' => $g->id])}}" class="btn-accion-tabla tooltipsC" title="Editar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                <form action="{{route('eliminar_dgu', ['id' => $g->id])}}" class="d-inline form-eliminar" method="POST">
                                    @csrf @method("delete")
                                    <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Eliminar este registro">
                                        <i class="fa fa-fw fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php $i++;?>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection