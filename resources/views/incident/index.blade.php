@extends("theme.$theme.layout")
@section('title')
Incidencias
@endsection

@section('styles1')
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/themes/smoothness/jquery-ui.css">
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
<script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
<script src="{{ asset('dt/jszip.min.js') }}"></script>
<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
<script src="{{ asset('dt/buttons.print.min.js') }}"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.3/jquery-ui.min.js"></script>
<script src="{{ asset('monthpicker/jquery.mtz.monthpicker.js') }}"></script>

<script>
    $(document).ready( function () {
        $('#tabla-data').DataTable({
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
                    {
                        extend: 'csv',
                        text: 'CSV'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel'
                    },
                    {
                        extend: 'print',
                        text: 'Imprimir'
                    }
                ]
        });
    });
</script>
<script>
    $('#yearmonth-picker').monthpicker();

    function setFilterType(fType) {
        document.getElementById("filter-type").value = fType;
    }

    function setFilterTypeClass() {
        this.filterType = <?php echo json_encode($filterType) ?>;
        this.filterType = this.filterType + "";
        switch (this.filterType) {
            case "1":
                var element = document.getElementById("monthBtn");
                element.classList.add("active");
                break;
            case "2":
                var element = document.getElementById("yearBtn");
                element.classList.add("active");
                break;
        
            default:
                break;
        }
    }

    setFilterTypeClass();
</script>
@endsection
@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">
                    @switch($incidentType)
                        @case(14)
                            Capacitaciones
                            @break
                        @case(2)
                            
                            @break
                        @default
                            Incidencias
                    @endswitch
                </h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:nolaborables"])
                <div class="row">
                    <div class="col-md-3 col-md-offset-9">
                        @if($incidentType == 14)
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{ route('crear_incidente', $incidentType) }}" class="btn btn-block btn-success btn-sm">
                                        <i class="fa fa-fw fa-plus-circle"></i> Nuevo
                                    </a>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-12">
                                <form action="{{ route($sroute, $incidentType) }}">
                                    @include('filters.monthyear', 
                                                ['monthYear' => $monthYear, 
                                                'sroute' => $sroute])
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="tabla-data">
                    <thead>
                        <tr>
                            <th>Tipo incidencia</th>
                            <th>Fecha inicial</th>
                            <th>Fecha final</th>
                            <th>Empleado</th>
                            {{-- <th class="width70"></th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $data)
                        <tr>
                            <td>{{$data->typeincident->name}}</td>
                            <td>{{$data->start_date}}</td>
                            <td>{{$data->end_date}}</td>
                            <td>{{$data->employee->name}}</td>
                            
                            {{-- <td>
                                <a href="{{route('editar_incidente', ['id' => $data->id])}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                <form action="{{route('eliminar_incidente', ['id' => $data->id])}}" class="d-inline form-eliminar" method="POST">
                                    @csrf @method("delete")
                                    <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Eliminar este registro">
                                        <i class="fa fa-fw fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </td> --}}
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection