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
        <form action="{{$routeStore}}" method="POST" id="incidentForm">
        <div class="modal-body">
                @csrf
                <div class="row">
                    <div class="col-md-3"><label for="typeIncident">Incidente:</label></div>
                    <div class="col-md-9">
                        <select name="typeIncident" v-model="selIncident" class="form-control" required>
                            <option v-for="incident in lIncidents" :value="incident.id">@{{incident.name}}</option>
                        </select>
                        <input type="hidden" name="employee_id" :value="employee_id">
                        <input type="hidden" name="date" :value="date">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
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
                <h3 class="box-title">Reporte incidencias empleados de {{$sStartDate}} a {{$sEndDate}}</h3>
                
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
                                    <th>Num empleado</th>
                                    <th>Empleado</th>
                                    <th>Fecha</th>
                                    <th>Prima dominical</th>
                                    <th>Incidencia</th>
                                    <th>Falta</th>
                                    <th>-</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lRows as $row)
                                        @switch($row->incident_type)
                                            @case(1)
                                            @case(2)
                                            @case(3)
                                            @case(4)
                                            @case(5)
                                            @case(6)
                                            @case(20)
                                                <tr>
                                                    <td style="background-color: #B388FF">{{$row->numEmployee}}</td>
                                                    <td style="background-color: #B388FF">{{$row->employee}}</td>
                                                    <td style="background-color: #B388FF">{{$row->outDate}}</td>
                                                    <td style="background-color: #B388FF">{{$row->isSunday > 0 ? $row->isSunday : ""}}</td>
                                                    <td style="background-color: #B388FF">{{$row->incident}}</td>
                                                    <td style="background-color: #B388FF">{{$row->hasAbsence ? "falta": ""}}</td>
                                                    <td style="background-color: #B388FF"></td>
                                                </tr>
                                                @break
                                            @case(8)
                                            @case(9)
                                            @case(18)
                                                <tr>
                                                    <td style="background-color: #80D8FF">{{$row->numEmployee}}</td>
                                                    <td style="background-color: #80D8FF">{{$row->employee}}</td>
                                                    <td style="background-color: #80D8FF">{{$row->outDate}}</td>
                                                    <td style="background-color: #80D8FF">{{$row->isSunday > 0 ? $row->isSunday : ""}}</td>
                                                    <td style="background-color: #80D8FF">{{$row->incident}}</td>
                                                    <td style="background-color: #80D8FF">{{$row->hasAbsence ? "falta": ""}}</td>
                                                    <td style="background-color: #80D8FF"></td>
                                                </tr>
                                                @break
                                            @case(10)
                                            @case(11)
                                            @case(16)
                                                <tr>
                                                    <td style="background-color: #EA80FC">{{$row->numEmployee}}</td>
                                                    <td style="background-color: #EA80FC">{{$row->employee}}</td>
                                                    <td style="background-color: #EA80FC">{{$row->outDate}}</td>
                                                    <td style="background-color: #EA80FC">{{$row->isSunday > 0 ? $row->isSunday : ""}}</td>
                                                    <td style="background-color: #EA80FC">{{$row->incident}}</td>
                                                    <td style="background-color: #EA80FC">{{$row->hasAbsence ? "falta": ""}}</td>
                                                    <td style="background-color: #EA80FC"></td>
                                                </tr>
                                                @break
                                            @case(7)
                                            @case(12)
                                            @case(13)
                                            @case(17)
                                            @case(19)
                                                <tr>
                                                    <td style="background-color: #B2FF59">{{$row->numEmployee}}</td>
                                                    <td style="background-color: #B2FF59">{{$row->employee}}</td>
                                                    <td style="background-color: #B2FF59">{{$row->outDate}}</td>
                                                    <td style="background-color: #B2FF59">{{$row->isSunday > 0 ? $row->isSunday : ""}}</td>
                                                    <td style="background-color: #B2FF59">{{$row->incident}}</td>
                                                    <td style="background-color: #B2FF59">{{$row->hasAbsence ? "falta": ""}}</td>
                                                    <td style="background-color: #B2FF59"></td>
                                                </tr>
                                                @break
                                            @case(14)
                                            @case(15)
                                                <tr>
                                                    <td style="background-color: #FFD180">{{$row->numEmployee}}</td>
                                                    <td style="background-color: #FFD180">{{$row->employee}}</td>
                                                    <td style="background-color: #FFD180">{{$row->outDate}}</td>
                                                    <td style="background-color: #FFD180">{{$row->isSunday > 0 ? $row->isSunday : ""}}</td>
                                                    <td style="background-color: #FFD180">{{$row->incident}}</td>
                                                    <td style="background-color: #FFD180">{{$row->hasAbsence ? "falta": ""}}</td>
                                                    <td style="background-color: #FFD180"></td>
                                                </tr>
                                                @break
                                            @default
                                            @if ($row->hasAbsence)
                                                <tr>
                                                    <td style="background-color: #FF8A80">{{$row->numEmployee}}</td>
                                                    <td style="background-color: #FF8A80">{{$row->employee}}</td>
                                                    <td style="background-color: #FF8A80">{{$row->outDate}}</td>
                                                    <td style="background-color: #FF8A80">{{$row->isSunday > 0 ? $row->isSunday : ""}}</td>
                                                    <td style="background-color: #FF8A80">{{$row->incident}}</td>
                                                    <td style="background-color: #FF8A80">{{$row->hasAbsence ? "falta": ""}}</td>
                                                    <td style="background-color: #FF8A80"><button type="button" v-on:click="showModal('{{$row->idEmployee}}', '{{$row->employee}}','{{$row->outDate}}');" class="btn btn-success" style="border-radius: 50%; padding: 3px 6px; font-size: 10px;"><span class="glyphicon glyphicon-plus"></span></button></td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td>{{$row->numEmployee}}</td>
                                                    <td>{{$row->employee}}</td>
                                                    <td>{{$row->outDate}}</td>
                                                    <td>{{$row->isSunday > 0 ? $row->isSunday : ""}}</td>
                                                    <td>{{$row->incident}}</td>
                                                    <td>{{$row->hasAbsence ? "falta": ""}}</td>
                                                    <td><button type="button" v-on:click="showModal('{{$row->idEmployee}}', '{{$row->employee}}','{{$row->outDate}}');" class="btn btn-success" style="border-radius: 50%; padding: 3px 6px; font-size: 10px;"><span class="glyphicon glyphicon-plus"></span></button></td>
                                                </tr>
                                            @endif
                                        @endswitch
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
            $('#incidentsTable').DataTable({
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
                "order": [[ 1, 'asc' ]],
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
                    ],
                "drawCallback": function ( settings ) {
                    var api = this.api();
                    var rows = api.rows( {page:'current'} ).nodes();
                    var last=null;
        
                    api.column(1, {page:'current'} ).data().each( function ( group, i ) {
                        if ( last !== group ) {
                            $(rows).eq( i ).before(
                                '<tr class="group"><td colspan="7" style="background-color: #9C9C9C;">'+group+'</td></tr>'
                            );
        
                            last = group;
                        }
                    } );
                }
            });    
        });
       </script>
       <script>
        function ServerData () {
            this.lIncidents = <?php echo json_encode($typeIncidents) ?>;
        }
        
        var oServerData = new ServerData();
        var oGui = new SGui();
    </script>
    <script src="{{ asset("assets/pages/scripts/incidentsEmployeesView/incidentsEmployees.js") }}" type="text/javascript"></script>
    @if (session('message'))
        <script>    
            oGui.showMessage('{{session('tittle')}}', '{{session('message')}}', '{{session('icon')}}');
        </script>    
    @endif
@endsection