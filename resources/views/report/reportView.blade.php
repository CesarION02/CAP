@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
    <style>
        tr {
            font-size: 70%;
        }
        span.nobr { white-space: nowrap; }
    </style>
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
            <div class="box-header with-border row">
                <div class="col-md-10">
                    @switch($tipo)
                        @case(1)
                            <h3 class="box-title">Reporte prenómina</h3>
                        @break
                        @case(2)
                            <h3 class="box-title">Reporte STPS</h3>
                            @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:reportechecadas"])
                        @break
                        @case(3)
                            <h3 class="box-title">Reporte prenómina</h3>
                        @break
                        <div class="box-tools pull-right">
                        </div>
                    @endswitch
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="sel-collaborator" id="sel-collaborator">
                        <option value="0" selected>Todos</option>
                        <option value="1">Empleados</option>
                        <option value="2">Practicantes</option>
                    </select>
                </div>
            </div>
            <div class="box-body" >
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Empleado</th>
                                    <th>Fecha entrada</th></th>
                                    <th>Hora entrada</th>
                                    <th>Fecha salida</th>
                                    <th>Hora salida</th>
                                    <th>Horas extra dobles</th>
                                    <th>Horas extra triples</th>
                                    <th>Prima dominical</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php $idEmployee = $lRows[0]->employee_id; $totalextrad = 0; $totaldominical = 0;$totalextrat = 0; if($payWay == 2){$idWeek = $lRows[0]->week;}else{ $idWeek = $lRows[0]->biweek;}?>
                                @for($i = 0 ; count($lRows) > $i ; $i++)
                                    @if($idEmployee != $lRows[$i]->employee_id || ( $lRows[$i]->week != $idWeek && $lRows[$i]->biweek != $idWeek) )
                                        <tr>
                                            <td>{{ $lRows[$i-1]->num_employee  }}</td>
                                            <td>{{ $lRows[$i-1]->name }}</td>
                                            <td>Totales:</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($totalextrad) }}</td>
                                            <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($totalextrat) }}</td>
                                            <td align="center">{{ $totaldominical }}</td>
                                            <td></td>
                                        </tr>
                                        <?php 
                                            $idEmployee = $lRows[$i]->employee_id; 
                                            if($payWay == 2){$idWeek = $lRows[$i]->week;}else{ $idWeek = $lRows[$i]->biweek;}
                                            $i--;
                                            $totaldominical = 0;
                                            $totalextrad = 0;
                                            $totalextrat = 0;
                                        ?>
                                        @continue
                                    @else
                                        @if($lRows[$i]->isOverJourney == false )
                                            <tr>
                                            <td>{{ $lRows[$i]->num_employee  }}</td>
                                            <td>{{ $lRows[$i]->name }}</td>
                                            @if ($lRows[$i]->hasAdjust == 1)
                                                @if($lRows[$i]->inDate != null)
                                                    <td>{{ \App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->inDate) }}</td>
                                                @else
                                                    <td></td>
                                                @endif
                                                <td>Se justifica la entrada</td>
                                                @if($lRows[$i]->outDate != null)
                                                    <td>{{ \App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->outDate) }}</td>
                                                @else
                                                    <td></td>
                                                @endif
                                                <td>Se justifica la salida</td>
                                                @if($tipo == 1)
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraDobleMins + $lRows[$i]->extraDobleMinsNoficial) }}</td>
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraTripleMins + $lRows[$i]->extraTripleMinsNoficial) }}</td>
                                                @elseif($tipo == 2)
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDobleMins) }}</td>
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraTripleMins) }}</td>
                                                @else
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$j]->extraDobleMins)}}</td>
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraTripleMinsNoficial) }}</td>
                                                @endif
                                                <?php $totalextrad = $totalextrad + $lRows[$i]->extraDobleMins; $totalextrat = $totalextrat + $lRows[$i]->extraTripleMins; ?>
                                                @if($lRows[$i]->is_sunday == 1)
                                                    <td align="center">{{"1"}}</td>
                                                    <?php $totaldominical = $totaldominical + 1; ?>
                                                @else
                                                    <td></td>
                                                @endif
                                            @elseif($lRows[$i]->haschecks == 1 && $lRows[$i]->is_dayoff == 0 && $lRows[$i]->is_holiday == 0 && $lRows[$i]->hasabsence == 0 )
                                                <td>{{ \App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->inDate) }}</td>
                                                @if($tipo != 2)
                                                    @if($lRows[$i]->inDateTime == '00:20:20')
                                                        <td>{{ 'Sin checada' }}</td>
                                                    @else
                                                        <td>{{ $lRows[$i]->inDateTime }}</td>
                                                    @endif
                                                @else
                                                    @if($lRows[$i]->inDateTimeNoficial == null)
                                                        @if($lRows[$i]->inDateTime == '00:20:20')
                                                            <td>{{ 'Sin checada' }}</td>
                                                        @else
                                                            <td>{{ $lRows[$i]->inDateTime }}</td>
                                                        @endif  
                                                    @else
                                                        <td>{{ $lRows[$i]->inDateTimeNoficial }}</td>
                                                    @endif
                                                @endif
                                        
                                                <td>{{ \App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->outDate) }}</td>
                                        
                                                @if($tipo != 2)
                                                
                                                    @if($lRows[$i]->outDateTime == '00:20:20')
                                                        <td>{{ 'Sin checada' }}</td>
                                                    @else
                                                        <td>{{ $lRows[$i]->outDateTime }}</td>
                                                    @endif
                                                @else
                                                    @if($lRows[$i]->outDateTimeNoficial == null)
                                                        @if($lRows[$i]->outDateTime == '00:20:20')
                                                            <td>{{ 'Sin checada' }}</td>
                                                        @else
                                                            <td>{{ $lRows[$i]->outDateTime }}</td>
                                                        @endif  
                                                    @else
                                                        <td>{{ $lRows[$i]->outDateTimeNoficial }}</td>
                                                    @endif
                                                @endif
                                                @if($tipo == 1)
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraDobleMins + $lRows[$i]->extraDobleMinsNoficial) }}</td>
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraTripleMins + $lRows[$i]->extraTripleMinsNoficial) }}</td>
                                                @elseif($tipo == 2)
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraDobleMins) }}</td>
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraTripleMins) }}</td>
                                                @else
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraDobleMinsNoficial) }}</td>
                                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($lRows[$i]->extraTripleMinsNoficial) }}</td>
                                                @endif
                                                <?php $totalextrad = $totalextrad + $lRows[$i]->extraDobleMins; $totalextrat = $totalextrat + $lRows[$i]->extraTripleMins; ?>
                                                @if($lRows[$i]->is_sunday == 1)
                                                    <td align="center">{{"1"}}</td>
                                                    <?php $totaldominical = $totaldominical + 1; ?>
                                                @else
                                                    <td> </td>
                                                @endif
                                            @elseif($lRows[$i]->hasabsence==1)
                                                @if($lRows[$i]->inDate != null)
                                                    <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->inDate)}}</td>
                                                @else
                                                    <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->outDate)}}</td>
                                                @endif
                                                <td>{{ 'Falta'}}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            @elseif($lRows[$i]->is_dayoff==1)
                                                @if($lRows[$i]->inDate != null)
                                                    <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->inDate)}}</td>
                                                @else
                                                    <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->outDate)}}</td>
                                                @endif
                                                <td>{{ 'Descanso'}}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            @elseif($lRows[$i]->is_holiday==1)
                                                @if($lRows[$i]->inDate != null)
                                                    <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->inDate)}}</td>
                                                @else
                                                    <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->outDate)}}</td>
                                                @endif
                                                <td>{{ 'Día festivo'}}</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            @else
                                            <?php $completo = 0; ?>
                                                @for( $j = 0 ; count($incidencias) > $j ; $j++)
                                                    @if ($lRows[$i]->employee_id == $incidencias[$j]->idEmp && ($lRows[$i]->inDate == $incidencias[$j]->Date || $lRows[$i]->outDate == $incidencias[$j]->Date))
                                                        <?php $completo = 1; ?>
                                                        @if($lRows[$i]->inDate != null)
                                                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->inDate)}}</td>
                                                        @else
                                                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->outDate)}}</td>
                                                        @endif
                                                        @switch($incidencias[$j]->tipo)
                                                            @case(1)
                                                                <td>{{'Inasistencia sin permiso'}}</td>
                                                                @break
                                                            @case(2)
                                                                <td>{{'Inasistencia con permiso sin goce'}}</td>
                                                                @break
                                                            @case(3)
                                                                <td>{{'Inasistencia con permiso con goce'}}</td>
                                                                @break
                                                            @case(4)
                                                                <td>{{'Inasistencia administrativa reloj checador'}}</td>
                                                                @break
                                                            @case(5)
                                                                <td>{{'Inasistencia administrativa suspensión'}}</td>
                                                                @break
                                                            @case(6)
                                                                <td>{{'Inasistencia administrativa otros'}}</td>
                                                                @break
                                                            @case(7)
                                                                <td>{{'Onomástico'}}</td>
                                                                @break
                                                            @case(8)
                                                                <td>{{'Riesgo de trabajo'}}</td>
                                                                @break
                                                            @case(9)
                                                                <td>{{'Enfermedad en general'}}</td>
                                                                @break
                                                            @case(10)
                                                                <td>{{'Maternidad'}}</td>
                                                                @break
                                                            @case(11)
                                                                <td>{{'Licencia por cuidados'}}</td>
                                                                @break
                                                            @case(12)
                                                                <td>{{'Vacaciones'}}</td>
                                                                @break
                                                            @case(13)
                                                                <td>{{'Vacaciones pendientes'}}</td>
                                                                @break
                                                            @case(14)
                                                                <td>{{'Capacitación'}}</td>
                                                                @break
                                                            @case(15)
                                                                <td>{{'Trabajo fuera planta'}}</td>
                                                                @break
                                                            @case(16)
                                                                <td>{{'Paternidad'}}</td>
                                                                @break
                                                            @case(17)
                                                                <td>{{'Día otorgado'}}</td>
                                                                @break
                                                            @case(18)
                                                                <td>{{'Inasistencia prescripción medica'}}</td>
                                                                @break
                                                        @endswitch
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        @break
                                                    @endif
                                                @endfor
                                                @if ($completo == 0)
                                                    @if($lRows[$i]->inDate != null)
                                                        <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->inDate)}}</td>
                                                    @else
                                                        <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRows[$i]->outDate)}}</td>
                                                    @endif
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>  
                                                @endif
                                            @endif
                                                
                                            </tr>
                                        @endif
                                    @endif
                                @endfor
                                <tr>
                                    <td>{{ $lRows[$i-1]->num_employee  }}</td>
                                    <td>{{ $lRows[$i-1]->name }}</td>
                                    <td>Totales:</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($totalextrad) }}</td>
                                    <td align="center">{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($totalextrat) }}</td>
                                    <td align="center">{{ $totaldominical }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                            <?php $cadenaregreso = 'datosreportestps/'.$reporttype.'/'.$tipo;?>
                            <button onclick="topFunction()" id="myBtn" title="Ir arriba">Ir arriba</button>
                            <a href="{{ $cadenaregreso }}"  id="newButton" title="Nuevo reporte">Nuevo reporte</a>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection

@section("scripts")

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
                "iDisplayLength": -1,
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

    <script>
        //Get the button:
        mybutton = document.getElementById("myBtn");
        theNewButton = document.getElementById("newButton");

        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                mybutton.style.display = "block";
                theNewButton.style.display = "block";
            } else {
                mybutton.style.display = "none";
                theNewButton.style.display = "none";
            }
        }

        // When the user clicks on the button, scroll to the top of the document
        function topFunction() {
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        }
    </script>
@endsection