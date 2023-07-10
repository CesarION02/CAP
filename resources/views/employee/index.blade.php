@extends("theme.$theme.layout")
@section('title')
    {{ (isset($becarios) && $becarios ? 'Practicantes' : 'Empleados') }}
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    
    <script src="{{asset("assets/pages/scripts/filter.js")}}" type="text/javascript"></script>
    
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
                <h3 class="box-title">{{ (isset($becarios) && $becarios ? 'Practicantes' : 'Empleados') }}</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:empleados"])
                <div class="row">
                    <div class="col-md-3 col-md-offset-9">
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('crear_empleado', (isset($becarios) && $becarios) ? 1 : 0) }}" class="btn btn-block btn-success btn-sm">
                                    <i class="fa fa-fw fa-plus-circle"></i> Nuevo
                                </a>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <form action="{{ route('empleado') }}">
                                <input type="hidden" id="ifilter" name="ifilter">
                                <div class="col-md-16">
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        @switch($iFilter)
                                            @case(1)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary active">Dept. pendiente</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary">Todos</button>
                                            @break
                                            @case(2)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary">Dept. pendiente</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary active">Todos</button>
                                            @break
                                        @endswitch
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
                            <th>Nombre {{ (isset($becarios) && $becarios ? 'practicante' : 'empleado') }}</th>
                            <th>Nombre corto</th>
                            <th>Número {{ (isset($becarios) && $becarios ? 'practicante' : 'empleado') }}</th>
                            <th>Política registro</th>
                            <th>Departamento CAP</th>
                            <th>Puesto</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $data)
                        <tr>
                            <td>{{$data->name}}</td>
                            <td>{{$data->short_name}}</td>
                            <td>{{$data->num_employee}}</td>
                            <td>{{$data->way_register == null ? "" : $data->way_register->name}}</td>
                            <td>{{$data->department == null ? "" : $data->department->name}}</td>
                            <td>{{$data->job == null ? "" : $data->job->name}}</td>
                            <td>
                                <a href="{{route('editar_empleado', ['id' => $data->id, 'becario' => (isset($becarios) && $becarios ? 1 : 0) ] )}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                @if(isset($becarios))
                                    <form action="{{route('eliminar_empleado', ['id' => $data->id])}}" class="d-inline form-eliminar" method="POST">
                                        @csrf @method("delete")
                                        <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Dar de baja este registro">
                                            <i class="fa fa-fw fa-trash text-danger"></i>
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