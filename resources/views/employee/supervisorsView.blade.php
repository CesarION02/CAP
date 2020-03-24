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
                <h3 class="box-title">Empleados</h3>
                <div class="box-tools pull-right">

                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Número empleado</th>
                            <th>Nombre empleado</th>
                            <th>Nombre corto</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                        <tr>
                            <td>{{$employee->numEmployee}}</td>
                            <td>{{$employee->nameEmployee}}</td>
                            <td>{{$employee->shortName}}</td>
                            <td>
                                <a href="{{route('editar_nombrecorto', ['id' => $employee->idEmployee])}}" class="btn-accion-tabla tooltipsC" title="Editar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
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