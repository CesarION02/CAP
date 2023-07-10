@extends("theme.$theme.layout")
@section('title')
Plantilla horarios
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/funciones.js")}}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/filter.js")}}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script>
        var oGui = new SGui();
    </script>
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
            "order": [[ 0, 'asc' ], [ 1, 'desc' ], [ 2, 'desc' ]],
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
<script>
    function deleteRegistry(form){
        event.preventDefault();
        (async () => {
            if (await oGui.confirm('','Desea eliminar el registro?','warning')) {
                form.submit();
            }
        })();
    }
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Asignar horario fijo departamento</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:asignacionpordept"])
                <div class="row">
                    <div class="col-md-3 col-md-offset-9">
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{route('deptoProgramming')}}" class="btn btn-block btn-info btn-sm">
                                    <i class="fa fa-fw fa-plus-circle"></i> Asignar horario
                                </a>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <form action="{{ route('index_deptoProgramming') }}">
                                <input type="hidden" id="ifilter" name="ifilter">
                                <div class="col-md-16">
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        @switch($iFilter)
                                            @case(1)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary active">Depto. sin horario</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary">Depto. con horario</button>
                                            @break
                                            @case(2)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary">Depto. sin horario</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary active">Depto. con horario</button>
                                            @break
                                        @endswitch
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Departamento</th>
                            <th>Fecha inicial</th>
                            <th>Fecha final</th>
                            <th>Horario</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assigns as $assign)
                            <tr>
                                <td>{{$assign->depto}}</td>
                                @if ($iFilter == 1)
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                @else
                                    <td>
                                        @if($assign->start_date == null)
                                            N/A
                                        @else
                                            {{\App\SUtils\SDateTimeUtils::orderDate($assign->start_date)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if($assign->end_date == null)
                                            N/A
                                        @else
                                            {{\App\SUtils\SDateTimeUtils::orderDate($assign->end_date)}}
                                        @endif
                                    </td>
                                    <td>{{$assign->schedule}}</td>
                                    <td>
                                        <a href="{{route('edit_deptoProgramming', ['deptoId' => $assign->deptoId])}}" class="btn-accion-tabla tooltipsC" title="Ver/Modificar este registro">
                                            <i class="fa fa-fw fa-pencil"></i>
                                        </a>
                                        <form action="{{route('delete_deptoProgramming', ['deptoId' => $assign->deptoId])}}" class="d-inline form-eliminar" method="POST" onsubmit="deleteRegistry(this);">
                                            @csrf @method("delete")
                                            <button class="btn-accion-tabla eliminar tooltipsC" title="Eliminar este registro" value="submit">
                                                <i class="fa fa-fw fa-trash text-danger"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection