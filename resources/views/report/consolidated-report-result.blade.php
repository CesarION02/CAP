@extends("theme.$theme.layoutcustom")
@section('styles1')
<link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
<style>
    tr {
        font-size: 70%;
    }

    span.nobr {
        white-space: nowrap;
    }
</style>
@endsection
@section('title')
Reporte consolidado
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border row">
                <div class="col-md-10">
                    <h3 class="box-title">Reporte consolidado. Periodo: <b>{{ $sStartDate." - ". $sEndDate}}</b></h3>
                    <div class="box-tools pull-right">
                    </div>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Empleado</th>
                                    @foreach ($aDates as $oDate)
                                        <th>Horario
                                            <span class="nobr">
                                                ({{ $oDate->format("d-m-Y") }})
                                            </span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lConsolidated as $row)
                                    <tr>
                                        <td>{{ str_pad($row->employee->num_employee, 6, "0", STR_PAD_LEFT) }}</td>
                                        <td><span class="nobr">{{ $row->employee->name }}</span></td>
                                        @foreach ($aDates as $oDate)
                                            @if ($row->lDates[$oDate->toDateString()]->schedule != null)
                                                {{-- <td>{{ serialize($row->lDates[$oDate->toDateString()]) }}</td> --}}
                                                <td>
                                                    <p>{{ ($row->lDates[$oDate->toDateString()]->schedule->auxIsSpecialSchedule ? "(TE) " : "").
                                                    $row->lDates[$oDate->toDateString()]->s_name }}</p>
                                                    @if ($row->lDates[$oDate->toDateString()]->in_time != "")
                                                        {{
                                                            
                                                            $row->lDates[$oDate->toDateString()]->in_time."-".
                                                            $row->lDates[$oDate->toDateString()]->out_time 
                                                        }} 
                                                    @else
                                                        {{
                                                            ""
                                                        }}  
                                                    @endif
                                                </td>
                                            @else
                                                <td>Sin horario</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
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

<script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>

<script>
    var oGui = new SGui();
    oGui.showLoading(5000);
</script>

<script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
<script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
<script src="{{ asset('dt/jszip.min.js') }}"></script>
<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
<script src="{{ asset('dt/buttons.print.min.js') }}"></script>
<script src="{{ asset("js/excel/xlsx.full.min.js") }}" type="text/javascript"></script>
<script src="{{ asset("js/excel/FileSaver.min.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>

<script>
    $(document).ready(function() {

            var oTable = $('#delays_table').DataTable({
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
                            extend: 'copy', text: 'copiar'
                            
                        },
                        {
                            extend: 'csv',
                            
                        },
                        {
                            extend: 'print', text: 'imprimir'
                            
                        }
                    ]
            });

            $('#sel-collaborator').change( function() {
                oTable.draw();
            });
        });
</script>
@endsection