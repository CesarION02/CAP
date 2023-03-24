@extends("theme.$theme.layout")
@section('styles1')
<style>
    tr {
        font-size: 70%;
    }

    span.nobr {
        white-space: nowrap;
    }
</style>
<link rel="stylesheet" href="{{ asset("assets/css/reportStepOne.css") }}">
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
                                <select id="typeIncident" name="typeIncident" v-model="oEvent.type_id" class="form-control" :disabled="isDisabled"
                                    required>
                                    <option v-for="incidentType in lTypeIncidentsList" :value="incidentType.id">@{{incidentType.name}}
                                    </option>
                                </select>
                                <input type="hidden" name="id_incident" :value="oEvent.id">
                                <input type="hidden" name="employee_id" :value="oEvent.employee_id">
                                <input type="hidden" name="date" :value="oEvent.start_date">
                                <input type="hidden" name="oldIncident" :value="oldIncident">
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="incidentComments">Comentarios:</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" name="comments" v-model="oEvent.nts" class="form-control" maxlength="254" :disabled="isDisabled">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div v-if="canDelete && ! isDisabled" class="col-md-2" style="float: left;">
                                <button type="button" id="btnDelete" class="btn btn-danger" data-dismiss="modal"
                                    :disabled="onSubmit" v-on:click="deleteIncident();">Eliminar</button>
                            </div>
                            <div class="col-md-4" style="float: right;">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                                <button v-if="! isDisabled" type="button" id="btnSubmit" class="btn btn-primary" data-dismiss="modal"
                                    :disabled="onSubmit" v-on:click="store();">Guardar</button>
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
                @if ($wizard != 2)
                    <div class="box-tools pull-right">
                        <a class="btn btn-success" href="{{$route}}">Nuevo reporte</a>
                    </div>
                @endif
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <table id="incidentsTable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="border: solid 1px rgb(86, 86, 86);">id empleado</th>
                                    <th style="border: solid 1px rgb(86, 86, 86);">Num empleado</th>
                                    <th style="border: solid 1px rgb(86, 86, 86);">Empleado</th>
                                    @foreach ($aDates as $date)
                                        <th style="border: solid 1px rgb(86, 86, 86);">
                                            <span class="nobr">
                                                {{ $date }}</th>
                                            </span>
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
                                <tr v-for="vRow in vRows">
                                    <td style="border: solid 1px rgb(86, 86, 86); text-align: center;">
                                        @{{ vRow.idEmployee }}
                                    </td>
                                    <td style="border: solid 1px rgb(86, 86, 86); text-align: center;">
                                        @{{ vueGui.pad(vRow.numEmployee, 6) }}
                                    </td>
                                    <td style="border: solid 1px rgb(86, 86, 86); text-align: center; font-weight: bold;">
                                        @{{ vRow.nameEmployee }}
                                        <span v-if="vRow.isVobo" aria-hidden="true" class="glyphicon glyphicon-ok" style="color:green;"></span>
                                    </td>
                                    {{-- <td v-for="date in vDates" style="border: solid 1px rgb(86, 86, 86); text-align: center; font-weight: bold;">
                                        <label for="">1</label>
                                    </td> --}}
                                    <th v-for="sDate in vDates" 
                                        :class="getCssClass(vRow.days[sDate].events, vRow.days[sDate].hasAbsence)" 
                                        :title="getTitle(vRow.days[sDate].events, vRow.days[sDate].hasAbsence)"
                                        style="border: solid 1px rgb(86, 86, 86); text-align: center; font-weight: bold;"
                                        v-on:click="showModal(vRow.idEmployee, vRow.nameEmployee, sDate, vRow.days[sDate].events, vRow.days[sDate].hasAbsence, vRow.isVobo)">
                                        @{{ getText(vRow.days[sDate].events, vRow.days[sDate].hasAbsence) }}
                                    </th>
                                    <td style="border: solid 1px rgb(86, 86, 86);">@{{ vRow.faltas }}</td>
                                    <td style="border: solid 1px rgb(86, 86, 86);">@{{ vRow.descansos }}</td>
                                    <td style="border: solid 1px rgb(86, 86, 86);">@{{ vRow.vacaciones }}</td>
                                    <td style="border: solid 1px rgb(86, 86, 86);">@{{ vRow.inasistencias }}</td>
                                    <td style="border: solid 1px rgb(86, 86, 86);">@{{ vRow.incapacidad }}</td>
                                    <td style="border: solid 1px rgb(86, 86, 86);">@{{ vRow.onomastico }}</td>
                                </tr>
                            </tbody>
                        </table>
                        @if( $wizard == 2)
                            <p>
                            <div class="row">
                                <div class="col-md-2"><b>Estatus proceso:</b> </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary" id="guardar" disabled>Anterior</button>
                                </div>
                                &nbsp;
                                <div class="col-md-2" style="font-size:20px;">
                                    &nbsp;<span class="label label-primary"> 1 </span>
                                    &nbsp;<span class="label label-default"> 2 </span>
                                    &nbsp;<span class="label label-default"> 3 </span>&nbsp;
                                </div>
                                <div class="col-md-1">
                                    <form action="{{route('reportetiemposextra')}}" autocomplete="off">
                                        <input type="hidden" name="start_date" value={{ $sStartDate }}>
                                        <input type="hidden" name="end_date" value={{ $sEndDate }}>
                                        <input type="hidden" name="emp_id" value="0">
                                        <input type="hidden" name="report_mode" value="2">
                                        <input type="hidden" name="delegation" value="{{ $bDelegation }}">
                                        <input type="hidden" name="id_delegation" value="{{ $iIdDelegation }}">
                                        <input type="hidden" name="pay_way" value={{ $payWay }}>
                                        <input type="hidden" name="wizard" value={{ $wizard }}>
                                        <button type="submit" class="btn btn-primary" id="guardar">Siguiente</button>
                                    </form>
                                </div>
                            </div>
                            </p>
                            <div class="row">
                                <div class="col-md-2"><a href="{{ route('inicio') }}" class="btn btn-danger">Cancelar</a>
                                </div>
                            </div>
                        @endif
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
<script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
<script>
    function ServerData () {
        this.lTypeIncidents = <?php echo json_encode($lTypeIncidents) ?>;
        this.lTypeCapIncidents = <?php echo json_encode($lTypeCapIncidents) ?>;
        this.routeDelete = <?php echo json_encode($routeDelete) ?>;
        this.routeStore = <?php echo json_encode($routeStore) ?>;
        this.lRows = <?php echo json_encode($lRows) ?>;
        this.aDates = <?php echo json_encode($aDates) ?>;
    }
        
    var oServerData = new ServerData();
    var oGui = new SGui();
    console.log(oServerData.lRows);

    for (const row of oServerData.lRows) {
        console.log(row);
    }
</script>
<script src="{{ asset("assets/pages/scripts/report/SFirstStepReport.js") }}" type="text/javascript"></script>
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
                        "searchable": false
                    }
                ],
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
                ]
            });
    });
</script>
@if (session('message'))
    <script>
        oGui.showMessage('{{ session('tittle') }}', '{{ session('message') }}', '{{ session('icon') }}')
    </script>
@endif
@endsection