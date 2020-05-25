@extends("theme.$theme.layout")
@section('title')
Empleados
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/datatable/indexFingerActivar.js")}}" type="text/javascript"></script>
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
                <h3 class="box-title">Empleados desactivados</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('huellas')}}" class="btn btn-block btn-success btn-sm">
                        <i class="fa fa-fw fa-check-circle"></i>Activos
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Número empleado</th>
                            <th>Nombre empleado</th>
                            <th>Manera de checar</th>
                            <th>Huella</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                        <tr>
                            <td>{{$employee->num}}</td>
                            <td>{{$employee->nameEmployee}}</td>
                            <td>{{$employee->way}}</td>
                            
                                @if($employee->fingerprint != null)
                                    <td style="background-color:green"><font color="white">Registrada</font></td>
                                @else
                                    <td style="background-color:red"><font color="white">No registrada</font></td>
                                @endif
                            
                            <td>
                                <form action="{{route('activar', ['id' => $employee->idEmployee])}}" class="d-inline form-eliminar" method="POST">
                                    @csrf @method("delete")
                                    <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Activar este registro">
                                        <i class="fa fa-fw fa-check-circle text-danger"></i>
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