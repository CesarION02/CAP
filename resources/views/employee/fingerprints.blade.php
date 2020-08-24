@extends("theme.$theme.layout")
@section('title')
Empleados
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/datatable/indexFingerActivar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/indexFinger.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
<script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.print.min.js') }}"></script>
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
                @switch($iFilter)
                    @case(1)
                        <h3 class="box-title">Huellas digitales empleados activos</h3>
                    @break
                    @case(2)
                        <h3 class="box-title">Huellas digitales empleados inactivos</h3>
                    @break
                    @case(3)
                        <h3 class="box-title">Huellas digitales empleados todos</h3>
                    @break
                @endswitch
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:huellas"])
                <div class="row">
                    <div class="col-md-3 col-md-offset-9">
                        <div class="row">
                            <div class="col-md-12">
                            
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <form action="{{ route('huellas') }}">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        @switch($iFilter)
                                            @case(1)
                                                <select class="form-control" name="filter_acts">
                                                <option value="1" selected>Activos</option>
                                                <option value="2">Inactivos</option>
                                                <option value="3">Todos</option>
                                                </select>

                                                @break
                                            @case(2)
                                                <select class="form-control" name="filter_acts">
                                                    <option value="1">Activos</option>
                                                    <option value="2" selected>Inactivos</option>
                                                    <option value="3">Todos</option>
                                                </select>
                                                @break
                                            @case(3)
                                                <select class="form-control" name="filter_acts">
                                                    <option value="1">Activos</option>
                                                    <option value="2">Inactivos</option>
                                                    <option value="3" selected>Todos</option>
                                                </select>
                                                @break
                                            @default
                                                
                                        @endswitch
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="submit">
                                                <i class="glyphicon glyphicon-search"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Número empleado</th>
                            <th>Empleado</th>
                            <th>Manera de checar</th>
                            <th>Huella digital</th>
                            <th>Estado</th>
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
                            @if($employee->is_delete == 0)
                                <td>Activo</td>
                            @else
                                <td>Inactivo</td>
                            @endif
                            <td>
                                <a href="{{route('editarhuella', ['id' => $employee->idEmployee])}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                @if($iFilter == 2)
                                <form action="{{route('activar', ['id' => $employee->idEmployee])}}" class="d-inline form-activar" method="POST">
                                    @csrf @method("delete")
                                    <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Activar este registro">
                                        <i class="fa fa-fw fa-check-circle text-danger"></i>
                                    </button>
                                </form>
                                @elseif($iFilter == 1)
                                <form action="{{route('desactivar', ['id' => $employee->idEmployee])}}" class="d-inline form-eliminar" method="POST">
                                    @csrf @method("delete")
                                    <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Desactivar este registro">
                                        <i class="fa fa-fw fa-times-circle text-danger"></i>
                                    </button>
                                </form>
                                @endif
                                
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