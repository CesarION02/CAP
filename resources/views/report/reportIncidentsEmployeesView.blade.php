@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
    <style>
        tr {
            font-size: 70%;
        }
        span.nobr { white-space: nowrap; }
    </style>
@endsection

@section('content')
<div class="row" id="incidentsEmployees">

<!-- Modal -->
<div id="incidentsModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">@{{date}} : @{{employee}}</h4>
        </div>
        <form method="POST" id="incidentForm">
        <div class="modal-body">
                @csrf
                <div class="row">
                    <div class="col-md-3"><label for="typeIncident">Incidente:</label></div>
                    <div class="col-md-9">
                        <select id="typeIncident" name="typeIncident" v-model="selIncident" class="form-control" required>
                            <option v-for="incident in lIncidents" :value="incident.id">@{{incident.name}}</option>
                        </select>
                        <input type="hidden" name="employee_id" :value="employee_id">
                        <input type="hidden" name="date" :value="date">
                        <input type="hidden" name="oldIncident" :value="oldIncident">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-2" style="float: left;">
                        <button type="button" id="btnDelete" class="btn btn-danger" data-dismiss="modal" :disabled="onSubmit" v-on:click="deleteIncident();">Eliminar</button>
                    </div>
                    <div class="col-md-4" style="float: right;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" id="btnSubmit" class="btn btn-primary" data-dismiss="modal" :disabled="onSubmit" v-on:click="store();">Guardar</button>
                    </div>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte prenómina de {{$sStartDate}} a {{$sEndDate}}</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:reporteincidenciasempleado"])
                <div class="box-tools pull-right">
                    <a class="btn btn-success" href="{{$route}}">Nuevo reporte</a>
                </div>
            </div>
            <div class="box-body" >
                <div class="row">
                    <div class="col-md-12">
                        <table id="incidentsTable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="border: solid 1px rgb(86, 86, 86);">id empleado</th>
                                    <th style="border: solid 1px rgb(86, 86, 86);">Num empleado</th>
                                    <th style="border: solid 1px rgb(86, 86, 86);">Empleado</th>
                                    @foreach ($aDates as $date)
                                        <th style="border: solid 1px rgb(86, 86, 86);"  >{{$date}}</th>
                                    @endforeach
                                    <th style="border: solid 1px rgb(86, 86, 86);">Faltas</th>
                                    <th style="border: solid 1px rgb(86, 86, 86);">Descansos</th>
                                    <th style="border: solid 1px rgb(86, 86, 86);">Vacaciones</th>
                                    <th style="border: solid 1px rgb(86, 86, 86);">Inasistencias</th>
                                    <th style="border: solid 1px rgb(86, 86, 86);">Incapacidad</th>
                                    <th style="border: solid 1px rgb(86, 86, 86);">Onomastico</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lRows as $row)
                                    <tr>
                                        <td style="border: solid 1px rgb(86, 86, 86); text-align: center;">{{$row->first()->idEmployee}}</td>
                                        <td style="border: solid 1px rgb(86, 86, 86); text-align: center;">{{$row->first()->numEmployee}}</td>
                                        <td style="border: solid 1px rgb(86, 86, 86); text-align: center; font-weight: bold;">{{$row->first()->employee}}</td>
                                        @foreach ($row as $r)
                                            @switch($r->incident_type)
                                                @case(1)
                                                @case(2)
                                                @case(3)
                                                @case(4)
                                                @case(5)
                                                @case(6)
                                                @case(20)
                                                    <td style="border: solid 1px rgb(86, 86, 86);
                                                        text-align: center; background-color: #B388FF;">
                                                        {{$r->incident}}
                                                    </td>
                                                    @break
                                                @case(8)
                                                @case(9)
                                                @case(18)
                                                    <td style="border: solid 1px rgb(86, 86, 86);
                                                        text-align: center; background-color: #80D8FF;">
                                                        {{$r->incident}}
                                                    </td>
                                                    @break
                                                @case(10)
                                                @case(11)
                                                @case(16)
                                                    <td style="border: solid 1px rgb(86, 86, 86);
                                                        text-align: center; background-color: #EA80FC;">
                                                        {{$r->incident}}
                                                    </td>
                                                    @break
                                                @case(7)
                                                @case(12)
                                                @case(13)
                                                @case(17)
                                                @case(19)
                                                    <td style="border: solid 1px rgb(86, 86, 86);
                                                        text-align: center; background-color: #B2FF59;">
                                                        {{$r->incident}}
                                                    </td>
                                                    @break
                                                @case(14)
                                                @case(15)
                                                    <td style="border: solid 1px rgb(86, 86, 86);
                                                        text-align: center; background-color: #FFD180;">
                                                        {{$r->incident}}
                                                    </td>
                                                    @break
                                                @case(-1)
                                                    <td style="border: solid 1px rgb(86, 86, 86);
                                                        text-align: center; background-color: #a4a4a4;">
                                                        -
                                                    </td>
                                                    @break
                                                @default
                                                    @if ($r->hasAbsence)
                                                        <td style="border: solid 1px rgb(86, 86, 86);
                                                            text-align: center; background-color: #FF8A80;">
                                                            Falta
                                                        </td>
                                                    @else
                                                        <td style="border: solid 1px rgb(86, 86, 86);"></td>
                                                    @endif
                                                    @break
                                            @endswitch
                                        @endforeach
                                        <td style="border: solid 1px rgb(86, 86, 86);">{{$row->faltas}}</td>
                                        <td style="border: solid 1px rgb(86, 86, 86);">{{$row->descansos}}</td>
                                        <td style="border: solid 1px rgb(86, 86, 86);">{{$row->vacaciones}}</td>
                                        <td style="border: solid 1px rgb(86, 86, 86);">{{$row->inasistencias}}</td>
                                        <td style="border: solid 1px rgb(86, 86, 86);">{{$row->incapacidad}}</td>
                                        <td style="border: solid 1px rgb(86, 86, 86);">{{$row->onomastico}}</td>
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
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            table = $('#incidentsTable').DataTable({
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
                "columnDefs": [
                    {
                        "targets": [0],
                        "visible": false,
                        "searchable": false,
                    }
                ],
                "scrollY": "500px",
                "scrollX": true,
                "fixedHeader": true,
                "colReorder": true,
                "order": [[ 2, 'asc' ]],
                "colReorder": true,
                "dom": 'Bfrtip',
                
                "lengthMenu": [
                    [ -1, 100, 50, 25, 10 ],
                    [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                ],
                "buttons": [
                        'csv', 
                        'excel', 
                        {
                            extend: 'print',
                            text: 'Imprimir'
                        }
                    ],
            });

            $('#incidentsTable tbody').on('click', 'td', function () {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                }
                else {
                    if(table.cell( this ).data().length == 0 || 
                    table.cell( this ).data() == "Falta" ||
                    table.cell( this ).data() == "CAPACITACIÓN" ||
                    table.cell( this ).data() == "TRABAJO FUERA PLANTA" ||
                    table.cell( this ).data() == "DESCANSO" || 
                    table.cell( this ).data() == "INASIST. TRABAJO FUERA DE PLANTA"){
                        
                        table.$('td.selected').removeClass('selected');
                        $(this).addClass('selected');
                        var cellIdx = table.cell( this ).index();
                        var rowIdx = table.cell( this ).index().row;
                        var data = table.row(rowIdx).data();
                        var title = table.column( cellIdx.column ).header();
                        appVue.showModal(data[0], data[2], $(title).html(), table.cell( this ).data());
                        event.stopPropagation();
                    }
                }
            });

            $('body').on('click', function () {
                table.$('td.selected').removeClass('selected');
            });
        });
       </script>
       <script>
        function ServerData () {
            this.lIncidents = <?php echo json_encode($typeIncidents) ?>;
            this.routeDelete = <?php echo json_encode($routeDelete) ?>;
            this.routeStore = <?php echo json_encode($routeStore) ?>;
        }
        
        var oServerData = new ServerData();
        var oGui = new SGui();
    </script>
    <script src="{{ asset("assets/pages/scripts/incidentsEmployeesView/incidentsEmployees.js") }}" type="text/javascript"></script>
    <script>
        var appVue = app;    
    </script>
    @if (session('message'))
        <script>    
            oGui.showMessage('{{session('tittle')}}', '{{session('message')}}', '{{session('icon')}}');
        </script>    
    @endif
@endsection