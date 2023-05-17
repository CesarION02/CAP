@extends("theme.$theme.layoutcustom")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/nv/datatables.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/css/button3d.css") }}">
    <link href="{{ asset("select2js/css/select2.min.css") }}" rel="stylesheet" />
    <style>
        tr {
            font-size: 65%;
        }
        span.nobr { white-space: nowrap; }
    </style>
@endsection
@section('title')
    {{ $sTitle }}
@endsection

@section('content')
<div class="row" id="reportDelayApp">

    @include('report.reportCommentsModal')

    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $sTitle }}</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:reportetiemposextra"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body">
                @include('report.adjustsModal')
                <div class="row">
                    @if ($isAdmin)
                        <div class="col-md-5">
                    @else   
                        <div class="col-md-8">
                    @endif
                        <p>Periodo: <b>{{ $sStartDate }}</b> - <b>{{ $sEndDate }}</b>. P. pago: <b>{{ $sPayWay }}</b>.</p>
                    </div>
                    @if($isAdmin)
                        <div class="col-md-3">
                            <label>Supervisores: </label>
                            <select class="select2-class" id="supervisores">
                                <option v-for="user in lUsers" :value="user.id">@{{user.name}}</option>
                            </select>
                        </div>
                    @endif
                    @if( isset($wizard) )
                        @if( $wizard != 2 )
                            <div class="col-md-2">
                                <a href="{{ route('generarreportetiemposextra') }}" target="_blank" class="btn btn-success">Nuevo reporte</a>
                            </div>
                        @endif
                    @else
                        <div class="col-md-2">
                            <a href="{{ route('generarreportetiemposextra') }}" target="_blank" class="btn btn-success">Nuevo reporte</a>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed" style="width:100%">
                        <thead>
                                <tr>
                                    <th title="Número de Colaborador">Num. Col.</th>
                                    <th title="Nombre del colaborador">Empleado</th>
                                    <th title="Comentarios">Com.</th>
                                    <th>Retardo (min)</th>
                                    <th title="Salida anticipada (minutos)">Antic. (min)</th>
                                    <th title="Total tiempo extra por pagar">TE p/pagar (hr)</th>
                                    <th title="Prima dominical">Pr. Dom.</th>
                                    <th title="Descansos">Desc.</th>
                                    <th title="Falta por ausencia de quecadas sin eventos">Faltas</th>
                                    <th title="Vacaciones">Vac.</th>
                                    <th title="Inasistencia por incidencia">Inasis.</th>
                                    <th title="Incapacidad">Incap.</th>
                                    {{-- <th title="Incidencias que se van a pagar">Incid. c/pago</th> --}}
                                    {{-- <th title="Incidencias sin pago (días descontados)">Incid. s/pago</th> --}}
                                    <th title="Días de prenómina, - faltas, - incidencias sin pago.">Días a pagar</th>
                                    <th title="Visto bueno individual">Vobo</th>
                                    <th>-</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in vData.lEmployees">
                                    <td>@{{ vueGui.pad(row.num_employee, 6) }}</td>
                                    <td>
                                    {{-- <td style="color: rgb(12, 135, 206);"> --}}
                                        <form action="{{ route('reportetiemposextra') }}" target="_blank">
                                            <a href='#' onclick='this.parentNode.submit(); return false;' title="Presiona para ver reporte a detalle">
                                                <span class="nobr">
                                                    <u>@{{ row.name }}</u>
                                                    <span aria-hidden="true" class="glyphicon glyphicon-eye-open" style="color:rgb(12, 135, 206);"></span>
                                                </span>
                                            </a>
                                            <input type="hidden" name="start_date" value="{{$sStartDate}}">
                                            <input type="hidden" name="end_date" value="{{$sEndDate}}">
                                            <input type="hidden" name="emp_id" :value="row.id">
                                            <input type="hidden" name="report_mode" value="2">
                                            <input type="hidden" name="optradio" value="employee">
                                            <input type="hidden" name="pay_way" value="{{$sPayWay}}">
                                        </form>
                                    </td>
                                    <td>
                                        <button type="button" href="#" v-on:click="getResumeComments(row.id)" title="Ver comentarios">
                                            <span class="glyphicon glyphicon-list-alt"></span>
                                        </button>
                                    </td>
                                    <td>@{{ row.entryDelayMinutes }}</td>
                                    <td>@{{ row.prematureOut }}</td>
                                    <td>@{{ vueGui.formatMinsToHHmm(row.extraHours == null || row.extraHours < 0 ? 0 : row.extraHours ) }}</td>
                                    <td>@{{ row.isSunday }}</td>
                                    <td>@{{ row.isDayOff }}</td>
                                    <td>@{{ row.hasAbsence }}</td>
                                    <td :title="row.vacations">@{{ row.countVacations }}</td>
                                    <td :title="row.absenceByEvent">@{{ row.countAbsenceByEvent }}</td>
                                    <td :title="row.inability">@{{ row.countInability }}</td>
                                    {{-- <td :title="row.payableEvents">@{{ row.countPayableEvents }}</td> --}}
                                    {{-- <td :title="row.noPayableEvents">@{{ row.countNoPayableEvents }}</td> --}}
                                    <td :title="(row.noPayableEvents)">@{{ row.countDaysToPay }}</td>
                                    <td v-if="vData.isPrepayrollInspection">
                                        <label class='container'>
                                            <span class="nobr">
                                                <span v-if="isVoboResume(row.num_employee) != undefined && isVoboResume(row.num_employee).is_vobo" title="Vobo aprobado" style="color: green" class="fa fa-check"></span>
                                                <span v-if="isVoboResume(row.num_employee) != undefined && isVoboResume(row.num_employee).is_rejected" title="Vobo rechazado" style="color: red;" class="fa fa-times"></span>
                                                <button v-if="isVoboResume(row.num_employee) === undefined || isVoboResume(row.num_employee).is_rejected" title="Aprobar Vobo" v-on:click="onChangeVoboResume($event, row.num_employee, 'vobo')" class="btn btn-success btn-sm btn3d"><i class="fa fa-thumbs-up fa-xs"></i></button>
                                                <button v-if="isVoboResume(row.num_employee) === undefined || isVoboResume(row.num_employee).is_vobo" title="Rechazar Vobo" v-on:click="onChangeVoboResume($event, row.num_employee, 'rejectM')" class="btn btn-danger btn-sm btn3d"><i class="fa fa-thumbs-down fa-xs"></i></button>
                                                <br>
                                                <div v-if="isVoboResume(row.num_employee) != undefined" style="font-size: 35%">
                                                    <span v-if="isVoboResume(row.num_employee).is_vobo">(Revisado por: <b>@{{ isVoboResume(row.num_employee).user_vobo_name }})</b></span>
                                                    <span v-if="isVoboResume(row.num_employee).is_rejected">(Rechazado por: <b>@{{ isVoboResume(row.num_employee).user_rejected_name }})</b></span>
                                                    <br v-if="isVoboResume(row.num_employee).is_rejected && isVoboResume(row.num_employee).comments.length > 0">
                                                    <span v-if="isVoboResume(row.num_employee).is_rejected && isVoboResume(row.num_employee).comments.length > 0">
                                                        @{{ isVoboResume(row.num_employee).comments }}
                                                    </span>
                                                </div>
                                            </span>
                                        </label>
                                    </td>
                                    <td v-else></td>
                                    <td>@{{ row.id }}</td>
                                </tr>
                            </tbody>
                            @if( isset($wizard) )
                                @if( $wizard != 2)
                                    <button onclick="reloadFunction()" id="reloadBtn" title="Recargar reporte">Actualizar</button>
                                    <button onclick="topFunction()" id="myBtn" title="Ir arriba">Ir arriba</button>
                                    <a href="{{ route('generarreportetiemposextra') }}" target="_blank" id="newButton" title="Nuevo reporte">Nuevo reporte</a>
                                @endif
                            @else
                                <button onclick="reloadFunction()" id="reloadBtn" title="Recargar reporte">Actualizar</button>
                                <button onclick="topFunction()" id="myBtn" title="Ir arriba">Ir arriba</button>
                                <a href="{{ route('generarreportetiemposextra') }}" target="_blank" id="newButton" title="Nuevo reporte">Nuevo reporte</a>
                            @endif
                        </table>
                        @if( isset($wizard))
                            @if( $wizard == 2)
                                <p>
                                    <div class="row">
                                        <div class="col-md-2"><b>Estatus proceso:</b> </div>
                                        <div class="col-md-1">
                                            <form action="{{route('reportetiemposextra')}}" autocomplete="off">
                                                <input type="hidden" name="start_date" value={{ $sStartDate }} >
                                                <input type="hidden" name="end_date" value={{ $sEndDate }} >
                                                <input type="hidden" name="emp_id" value="0" >
                                                <input type="hidden" name="report_mode" value="2" >
                                                <input type="hidden" name="delegation" value="0" >
                                                <input type="hidden" name="pay_way" value={{ $pay_way }} >
                                                <input type="hidden" name="delegation" value="{{ $bDelegation }}" >
                                                <input type="hidden" name="id_delegation" value="{{ $idDelegation }}" >
                                                <input type="hidden" name="wizard" value={{ $wizard }} >
                                                <input type="hidden" name="filter_employees" id="filter_employees" value=0> 
                                                <button type="submit" class="btn btn-primary" id="guardar">Anterior</button>
                                            </form>
                                        </div>
                                        &nbsp;
                                        <div class="col-md-2" style="font-size:20px;">
                                            &nbsp;<span class="label label-default"> 1 </span> 
                                            &nbsp;<span class="label label-default"> 2 </span> 
                                            &nbsp;<span class="label label-primary"> 3 </span>&nbsp;    
                                        </div>        
                                        <div class="col-md-1">
                                            <button type="submit" class="btn btn-primary" id="guardar" disabled>Siguiente</button>
                                        </div>
                                    </div>
                                </p>
                                <p>
                                    @include('report.reportRejectVoboModal')
                                    @include('report.reportvobototal')
                                </p>
                            @endif
                        @endif
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
<script src="{{ asset("dt/nv/datatables.js") }}" type="text/javascript"></script>
<script src="{{ asset("js/excel/xlsx.full.min.js") }}" type="text/javascript"></script>
<script src="{{ asset("js/excel/FileSaver.min.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
<script src="{{ asset('select2js/js/select2.min.js') }}"></script>

<script>
    function GlobalData () {

        this.lRows = <?php echo json_encode($lRows) ?>;
        this.lCommentsAdjsTypes = <?php echo json_encode($lCommentsAdjsTypes) ?>;
        this.lEmployees = <?php echo json_encode($lEmployees) ?>;
        this.lEmpWrkdDays = <?php echo json_encode($lEmpWrkdDays) ?>;
        this.adjTypes = <?php echo json_encode($adjTypes) ?>;
        this.lAdjusts = <?php echo json_encode($lAdjusts) ?>;
        this.tReport = <?php echo json_encode($tReport) ?>;
        this.lEmpVobos = <?php echo json_encode($lEmpVobos) ?>;
        this.isPrepayrollInspection = <?php echo json_encode($isPrepayrollInspection) ?>;
        this.oPrepayrollCtrl = <?php echo json_encode($oPrepayrollCtrl) ?>;
        this.registriesRoute = <?php echo json_encode($registriesRoute) ?>;
        this.REP_HR_EX = <?php echo json_encode(\SCons::REP_HR_EX) ?>;
        this.REP_DELAY = <?php echo json_encode(\SCons::REP_DELAY) ?>;
        this.ADJ_CONS = <?php echo json_encode(\SCons::PP_TYPES) ?>;
        this.subEmployees = <?php echo json_encode($subEmployees) ?>;
        this.lUsers = <?php echo json_encode($lUsers) ?>;
        this.routegetDirectEmployees = <?php echo json_encode(route('getDirectEmployees')) ?>;

        // this.minsCol = this.tReport == this.REP_DELAY ? 4 : 4;
        this.minsCol = 5;
        this.minsBeforeCol = 8;
        this.minsDelayCol = this.tReport == this.REP_DELAY ? 4 : 7;
        this.sunCol = 9;
        this.dayoffCol = 10;
        this.hiddenColEmId = 14;
        this.hiddenColExId = 14;
        this.hiddenCol = this.tReport == this.REP_DELAY ? 5 : 5;
        this.toExport = this.tReport == this.REP_DELAY ? [0, 1, 2, 3, 4, 6] : [0, 1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12];

        for(let i = 0; i<this.lEmployees.length; i++){
            // this.lEmployees[i].extraHours = convertToHoursMins(this.lEmployees[i].extraHours);
        }
        this.filterEmployee = <?php echo json_encode($filter_employees) ?>;
    }
    
    var oData = new GlobalData();
</script>
@include('prepayrollcontrol.checkgroup')
@include('prepayrollcontrol.checkprevious')
<script src="{{asset("assets/pages/scripts/report/DelayReport.js")}}" type="text/javascript"></script>
<script>
    $.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');

    var oTable = $('#delays_table').DataTable({
            language: {
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
            fixedHeader: true,
            order: [[0, 'asc']],
            columnDefs: [
                    {
                        targets: [ oData.hiddenColEmId ],
                        visible: false,
                        searchable: true,
                    }
                ],
            
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
        
        $('#delays_table_filter').prepend('<label for="directos">Empleados: </label>' +
                    '<select id="directos" class="select2-class" style="width: 35%">' +
                        '<option value="0">Todos</option>' +
                        '<option value="1">Directos</option>' +
                    '</select>' +
                    '&nbsp'
        );

        $('#sel-collaborator').change( function() {
            oTable.draw();
        });
</script>
<script>
        $(document).ready(function() {
            $('.select2-class').select2();

            $('#directos').on('select2:select', function (e) {
                if (e.params.data.id == 1) {
                    var searchValues = "";
                    for (let i = 0; i < oData.subEmployees.length; i++) {
                        searchValues = searchValues + '^' + oData.subEmployees[i] + '$' + (i < (oData.subEmployees.length-1) ? "|" : "");
                    }
                    console.log(searchValues, oData.subEmployees);
                    oTable.column(15).search("(" + searchValues + ")", true, false).draw();
                }
                else {
                    oTable.columns().search('').draw();
                }
            });

            setTimeout(() => {
                const elem = document.getElementById("sugerencia");
                if (typeof(elem) != 'undefined' && elem != null) {
                    elem.parentNode.removeChild(elem);
                }
            }, 15000);

            if(oData.filterEmployee == 0){
                oTable.columns().search('').draw();  
            }else{
                var searchValues = "";
                    for (let i = 0; i < oData.subEmployees.length; i++) {
                        searchValues = searchValues + '^' + oData.subEmployees[i] + '$' + (i < (oData.subEmployees.length-1) ? "|" : "");
                    }
                    console.log(searchValues, oData.subEmployees);
                    oTable.column(15).search("(" + searchValues + ")", true, false).draw();
            }

            if(oData.filterEmployee == 0){
                $('#directos').val('0').trigger('change.select2');  
                document.getElementById("filter_employees").value = 0; 
                
                $('#incidentsTable_filter').prepend('<label for="directos">Empleados: </label>' +
                        '<select id="directos" class="select2-class" style="width: 25%">' +
                            '<option selected value="0">Todos</option>' +
                            '<option value="1">Directos</option>' +
                        '</select>' +
                        '&nbsp'
                );
            }else{
                $('#directos').val('1').trigger('change.select2');
                document.getElementById("filter_employees").value = 1; 

                $('#incidentsTable_filter').prepend('<label for="directos">Empleados: </label>' +
                        '<select id="directos" class="select2-class" style="width: 25%">' +
                            '<option value="0">Todos</option>' +
                            '<option selected value="1">Directos</option>' +
                        '</select>' +
                        '&nbsp'
                );
            }
        });
    </script>
    @if($isAdmin)
        <script>
            $(document).ready(function() {
                $('#supervisores').on('select2:select', function (e) {
                    $('#directos').val(0).trigger('change');
                    oTable.columns().search('').draw();
                    axios.post(oData.routegetDirectEmployees, {
                        id: e.params.data.id
                    })
                    .then(res => {
                        console.log(res.data);
                        oData.subEmployees = res.data;
                    })
                    .catch(function(error) {
                        console.log(error);
                    });
                });
            })
        </script>
    @endif
    @if ($isPrepayrollInspection && isset($oPrepayrollCtrl) && !is_null($oPrepayrollCtrl))
        <script type="text/javascript">
            var isIndividualVobo = false;
            function onRejectSubmit() {
                if (oData.oPrepayrollCtrl.is_vobo || (!oData.oPrepayrollCtrl.is_rejected && !oData.oPrepayrollCtrl.is_vobo)) {
                    $('#rejectReason').val('');
                    $('#rejectModalId').modal('show');
                    isIndividualVobo = false;
                }
            }
            // al hacer click en el boton de rechazar
            $('#idRejectButton').on('click', function() {
                if (isIndividualVobo) {
                    return;
                }

                // se obtiene el comentario
                let rejectReason = $('#rejectReason').val();
                // se valida que el comentario

                $('#rejectModalId').modal('hide');

                // set de comentario en el formulario
                $('#form_vobo').find('#reject_reason').val(rejectReason);

                oGui.showLoading(10000);

                // submit de formulario
                $('#form_vobo').submit();
            });
        </script>
    @endif
<script>
    

    // When the user clicks on the button, scroll to the top of the document
    function topFunction() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    }

    function getRowsInNumEmployee(numEmployee) {
        var data = app.vData.lRows;
        var dataEmployee = [];

        for (let i = 0; i < data.length; i++) {
            if(data[i]['numEmployee'] == numEmployee){
                dataEmployee.push(data[i]);
            }
        }

        return dataEmployee;
    }

    function checkAdjust(dataEmployee) {
        for (let i = 0; i < dataEmployee.length; i++) {
            if (dataEmployee[i]['isDayChecked'] == true && dataEmployee[i]['adjusts'].length < 1) {
                return [false, dataEmployee[i]['inDateTime'], dataEmployee[i]['outDateTime']];
            }
        }

        return [true,null,null];
    }

    var iNumEmployee = 0;
    // onclick idRejectButton
    $('#idRejectButton').click(function() {
        // cerrar modal
        $('#rejectModalId').modal('hide');
        if (isIndividualVobo) {
            handleChangeCheck(null, iNumEmployee, 'reject');
            isIndividualVobo = false;
        }
    });

    /**
     * @param event event 
     * @param int numEmployee
     * @param string sOperation
     */
    function handleChangeCheck(event, numEmployee, sOperation) {
        var dataEmployee = getRowsInNumEmployee(numEmployee);
        let url = "{{ route('employee_vobo') }}";

        if (sOperation == 'rejectM') {
            // show modal
            iNumEmployee = numEmployee;
            isIndividualVobo = true;
            $('#rejectReason').val('');
            $('#rejectModalId').modal('show');
            return;
        }

        var result = sOperation == 'vobo' ? checkAdjust(dataEmployee) : new Array(true);
        if (result[0]) {
            oGui.showLoading(3000);
            axios.post(url, {
                _token: "{{ csrf_token() }}",
                num_employee: numEmployee,
                is_vobo: sOperation == 'vobo',
                is_reject: sOperation == 'reject',
                start_date: "{{ $sStartDate }}",
                end_date: "{{ $sEndDate }}",
                comments: sOperation == 'reject' ? $('#rejectReason').val() : ''
            })
            .then(res => {
                if (res.data.success) {
                    app.vData.lEmpVobos = res.data.lvobos;
                    oGui.showMessage(res.data.title, res.data.message, res.data.icon);
                }
                else {
                    oGui.showMessage(res.data.title, res.data.message, res.data.icon);
                }
            })
            .catch(function(error) {
                oGui.showError(error);
            });
        }
        else {
            oGui.showError('Falta comentario para la fecha:\n' + 'Entrada: ' + app.vueGui.formatDate(result[1]) + ' Salida: ' +app.vueGui.formatDate(result[2]));
        }
    }
</script>

<script src="{{asset("assets/pages/scripts/report/SReportRow.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/report/SExportExcel.js")}}" type="text/javascript"></script>
@endsection