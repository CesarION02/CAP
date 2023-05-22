@extends("theme.$theme.layoutcustom")
@section('styles1')
<link rel="stylesheet" href="{{ asset("dt/nv/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
<style>
    tr {
        font-size: 70%;
    }
    span.nobr { white-space: nowrap; }
</style>
@endsection
@section('title')
    Reporte de registros
@endsection
@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/funciones.js")}}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    <script src="{{ asset("dt/nv/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script>
        //Get the button:
        mybutton = document.getElementById("myBtn");
        theNewButton = document.getElementById("newButton");

        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
            if (document.body.scrollTop > 5 || document.documentElement.scrollTop > 5) {
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
<script>
    var iReportType = <?php echo json_encode($reportType) ?>;

    $(document).ready( function () {
        $.fn.dataTable.moment('DD/MM/YYYY');

        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                // var min = parseInt( $('#min').val(), 10 );
                let collaboratorVal = parseInt( $('#sel-collaborator').val(), 10 );
                let externalId = 0;

                switch (collaboratorVal) {
                    case 0:
                        return true;

                    case 1:
                        externalId = parseInt( data[iReportType != 4 ? 8 : 7] );
                        return externalId > 0;

                    case 2:
                        externalId = parseInt( data[iReportType != 4 ? 8 : 7] );
                        return ! (externalId > 0);

                    default:
                        break;
                }

                return false;
            }
        );

        var oTable = $('#checks_table').DataTable({
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
            columnDefs: [
                    {
                        targets: [ iReportType != 4 ? 8 : 7 ],
                        visible: false
                    }
                ],
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

        $('#sel-collaborator').change( function() {
            oTable.draw();
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
            <div class="box-header with-border row">
                <div class="col-md-10">
                    @if ($reportType == 1)
                        <h3 class="box-title">Reporte entrada/salida a 1 línea por área</h3>
                        @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:reportees"])
                    @elseif($reportType == 2)
                        <h3 class="box-title">Reporte entrada/salida a 1 línea por grupo depto.</h3>
                        @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:reportees"])
                    @elseif($reportType == 3)
                        <h3 class="box-title">Reporte entrada/salida a 1 línea por depto. CAP</h3>
                        @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:reportees"])
                    @else
                        <h3 class="box-title">Reporte entrada/salida a 1 línea por empleado</h3>
                        @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:reportees"])
                    @endif
                    
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="sel-collaborator" id="sel-collaborator">
                        <option value="0" selected>Todos</option>
                        <option value="1">Empleados</option>
                        <option value="2">Practicantes</option>
                    </select>
                </div>
            </div>
            <div class="box-body" id="reportApp">
                <div class="row">
                    <div class="col-md-12">
                        <table id="checks_table" class="table stripe table-condensed" style="width:100%">
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
                                            @default
                                            @break
                                    @endswitch
                                    <th>Obser.</th>
                                    <th>external_id</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0 ; count($lRegistries) > $i ; $i++)
                                    <tr class="{{ $lRegistries[$i]->type_id ==  2 ? \App\SUtils\SReportsUtils::getCssClass($lRegistries[$i]) : (
                                    (($i+1) < count($lRegistries) && $lRegistries[$i]->num_employee == $lRegistries[$i+1]->num_employee && $lRegistries[$i+1]->type_id == 2) ? 
                                        \App\SUtils\SReportsUtils::getCssClass($lRegistries[$i+1]) : ""
                                    ) }}">
                                        @if($lRegistries[$i]->type_id == 1)
                                            <td>{{$lRegistries[$i]->num_employee}}</td>
                                            <td>{{$lRegistries[$i]->name}}</td>
                                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($lRegistries[$i]->date)}}</td>
                                            <td>{{$lRegistries[$i]->time}}</td>
                                            @if(($i+1) < count($lRegistries))
                                                <?php $diaActual = \Carbon\Carbon::parse($lRegistries[$i]->date)  ?>
                                                <?php $diaAdelantado = \Carbon\Carbon::parse($lRegistries[$i+1]->date)  ?>
                                                <?php $diferencia = $diaActual->diffInDays($diaAdelantado)  ?>
                                              
                                                @if($lRegistries[$i]->num_employee == $lRegistries[$i+1]->num_employee && $lRegistries[$i+1]->type_id == 2 && $diferencia < 1)
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
                                            @default
                                            @break
                                        @endswitch
                                        <td>{{ isset($lRegistries[$i]->others) ? $lRegistries[$i]->others : '' }}</td>
                                        <td>{{ $lRegistries[$i]->external_id }}</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                        <button onclick="topFunction()" id="myBtn" title="Ir arriba">Ir arriba</button>
                        <a href="{{route('reporteES', ['id' => $routeType])}}" target="_blank" id="newButton" title="Nuevo reporte">Nuevo reporte</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection

