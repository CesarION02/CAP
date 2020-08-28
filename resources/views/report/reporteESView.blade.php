@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
@endsection
@section('title')
Reporte de registros
@endsection
@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/funciones.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
<script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.print.min.js') }}"></script>
<script>
    $(document).ready( function () {
        $.fn.dataTable.moment('DD/MM/YYYY');
        $('#checks_table').DataTable({
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
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte ES</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body" id="reportApp">
                <div class="row">
                    <div class="col-md-12">
                        <table id="checks_table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Empleado</th>
                                    <th>Fecha entrada</th>
                                    <th>Hora entrada</th>
                                    <th>Fecha salida</th>
                                    <th>Hora salida</th>
                                    @switch($reportType)
                                            @case(1)
                                                <th>Área</th>
                                            @break
                                            @case(2)
                                                <th>Grupo</th>
                                            @break
                                            @case(3)
                                                <th>Departamento</th>
                                            @break
                                            @case(4)
                                            @break
                                    @endswitch
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0 ; count($lRegistries) > $i ; $i++)
                                    <tr>
                                        @if($lRegistries[$i]->type_id == 1)
                                            <td>{{$lRegistries[$i]->num_employee}}</td>
                                            <td>{{$lRegistries[$i]->name}}</td>
                                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRegistries[$i]->date)}}</td>
                                            <td>{{$lRegistries[$i]->time}}</td>
                                            @if(($i+1) < count($lRegistries))
                                                @if($lRegistries[$i]->num_employee == $lRegistries[$i+1]->num_employee && $lRegistries[$i+1]->type_id == 2)
                                                    <?php $i++; ?>
                                                    <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRegistries[$i]->date)}}</td>
                                                    <td>{{$lRegistries[$i]->time}}</td>
                                                @else
                                                    <td>--</td>
                                                    <td>--</td>   
                                                @endif
                                            @else
                                                <td>--</td>
                                                <td>--</td>
                                        @endif
                                        @else
                                            <td>{{$lRegistries[$i]->num_employee}}</td>
                                            <td>{{$lRegistries[$i]->name}}</td>
                                            <td>--</td>
                                            <td>--</td>
                                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRegistries[$i]->date)}}</td>
                                            <td>{{$lRegistries[$i]->time}}</td>
                                        @endif
                                        
                                        @switch($reportType)
                                            @case(1)
                                                <td>{{$lRegistries[$i]->areaname}}</td>
                                            @break
                                            @case(2)
                                                <td>{{$lRegistries[$i]->groupname}}</td>
                                            @break
                                            @case(3)
                                                <td>{{$lRegistries[$i]->depname}}</td>
                                            @break
                                            @case(4)
                                            @break
                                        @endswitch
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection

