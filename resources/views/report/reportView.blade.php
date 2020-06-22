@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
@endsection
@section('title')
{{ $sTitle }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte de la STPS</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body" >
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed" style="width:100%">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Empleado</th>
                                    {{-- <th>Fecha entrada</th> --}}
                                    <th>Fecha entrada</th></th>
                                    <th>Hora entrada</th>
                                    <th>Fecha salida</th>
                                    <th>Hora salida</th>
                                    {{-- <th v-if="oData.tReport == oData.REP_DELAY">Retardo (min)</th>
                                    <th v-else>Horas Extra</th> --}}
                                    <th>Horas Extra Dobles</th>
                                    {{-- <th v-if="oData.tReport == oData.REP_HR_EX">Hr_progr_Sal</th> --}}
                                    <th>Horas Extra Triples</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0 ; count($lRows) > $i ; $i++)
                                <tr>
                                    <td>{{ $lRows[$i]->num_employee  }}</td>
                                    <td>{{ $lRows[$i]->name }}</td>
                                    {{-- <td>@{{ row.inDate }}</td> --}}
                                    <td>{{ $lRows[$i]->inDate }}</td>
                                    @if($lRows[$i]->haschecks == 1)
                                        @if($tipo != 2)
                                            <td>{{ $lRows[$i]->inDateTime }}</td>
                                        @else
                                            @if($lRows[$i]->inDateTimeNoficial == null)
                                                <td>{{ $lRows[$i]->inDateTime }}</td>   
                                            @else
                                                <td>{{ $lRows[$i]->inDateTimeNoficial }}</td>
                                            @endif
                                        @endif
                                    
                                        <td>{{ $lRows[$i]->outDate }}</td>
                                    
                                        @if($tipo != 2)
                                            <td>{{ $lRows[$i]->outDateTime }}</td>
                                        @else
                                            @if($lRows[$i]->outDateTimeNoficial == null)
                                                <td>{{ $lRows[$i]->outDateTime }}</td>   
                                            @else
                                                <td>{{ $lRows[$i]->outDateTimeNoficial }}</td>
                                            @endif
                                        @endif
                                    {{-- <td v-if="oData.tReport == oData.REP_DELAY">@{{ row.delayMins }}</td>
                                    <td v-else>@{{ row.extraHours }}</td> --}}
                                        @if($tipo == 1)
                                            <td align="center">{{ $lRows[$i]->extraDobleMins + $lRows[$i]->extraDobleMinsNoficial }}</td>
                                            <td align="center">{{ $lRows[$i]->extraTripleMins + $lRows[$i]->extraTripleMinsNoficial }}</td>
                                        @elseif($tipo == 2)
                                            <td align="center">{{ $lRows[$i]->extraDobleMins }}</td>
                                            <td align="center">{{ $lRows[$i]->extraTripleMins }}</td>
                                        @else
                                            <td align="center">{{ $lRows[$i]->extraDobleMinsNoficial }}</td>
                                            <td align="center">{{ $lRows[$i]->extraTripleMinsNoficial }}</td>
                                        @endif
                                    @elseif($lRows[$i]->hasabsence)
                                        <td>{{ 'Falta'}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @elseif($lRows[$i]->is_dayoff)
                                        <td>{{ 'Descanso'}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @elseif($lRows[$i]->is_holiday)
                                        <td>{{ 'Día festivo'}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endif
                                    {{-- <td v-if="oData.tReport == oData.REP_HR_EX">@{{ row.outDateTimeSch }}</td> --}}
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

@section("scripts")

    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.print.min.js') }}"></script>

        



    <script>
        $(document).ready(function() {
            $('#delays_table').DataTable({
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
                "scrollX": true,
                "dom": 'Bfrtip',
                "lengthMenu": [
                    [ 10, 25, 50, 100, -1 ],
                    [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                ],
                "buttons": [
                        'pageLength',
                        {
                            extend: 'excel',
                            
                        },
                        {
                            extend: 'copy',
                            
                        },
                        {
                            extend: 'csv',
                            
                        },
                        {
                            extend: 'print',
                            
                        }
                    ]
            });
        });
    </script>
@endsection