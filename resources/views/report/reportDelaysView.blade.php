@extends("theme.$theme.layoutcustom")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/nv/datatables.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/css/button3d.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
    <link href="{{ asset("select2js/css/select2.min.css") }}" rel="stylesheet" />
    <style>
        tr {
            font-size: 70%;
        }
        .tc {
            text-align: center;
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
            <div class="box-header with-border">
                <h3 class="box-title">{{ $sTitle }}</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:reportetiemposextra"])
                <div id="sugerencia" class="box-tools pull-right" style="background-color: #555; text-align: center;
                border-radius: 6px; padding: 8px 0; bottom: 125%; left: 50%; margin-left: -80px;">
                    <small style="color: white" class="text-muted">&nbsp;<b>Sugerencia:</b> Haz click en el botón <span class="glyphicon glyphicon-menu-hamburger"></span> para modificar la vista.&nbsp;</small>
                    <a href="#" class="glyphicon glyphicon-remove-sign" onclick="closeSugerencia();" style="color: white"></a>&nbsp;
                </div>
            </div>
            <div class="box-body" id="reportDelayApp">
                @include('report.adjustsModal')
                @include('report.reportRejectVoboModal')
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
                    @if(isset($wizard))
                        @if( $wizard != 2)
                            <div class="col-md-2" style="text-align: right">
                                <a href="{{ route('generarreportetiemposextra') }}" target="_blank" class="btn btn-success">Nuevo reporte</a>
                            </div>
                        @endif
                    @else
                        <div class="col-md-2" style="text-align: right">
                            <a href="{{ route('generarreportetiemposextra') }}" target="_blank" class="btn btn-success">Nuevo reporte</a>
                        </div>
                    @endif
                    <div class="col-md-2" style="text-align: center">
                        <div id="wrapper">
                            <button class="btn btn-info" id="button-a">Crear Excel</button>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Num. Col.</th>
                                    <th>Empleado</th>
                                    {{-- <th>Fecha entrada</th> --}}
                                    <th><span class="nobr">Fecha-hora</span> entrada</th>
                                    {{-- <th>Fecha salida</th> --}}
                                    <th><span class="nobr">Fecha-hora</span> salida</th>
                                    {{-- <th v-if="vData.tReport == vData.REP_DELAY">Retardo (min)</th>
                                    <th v-else>Horas Extra</th> --}}
                                    <th>Horario</th>
                                    <th>retardo (min entero) [oculta]</th>
                                    <th class="tc">TE jornada (hr)</th>
                                    <th class="tc">TE trabajado (hr)</th>
                                    <th class="tc">TE ajustado (hr)</th>
                                    <th class="tc">TE total (hr)</th>
                                    {{-- <th v-if="vData.tReport == vData.REP_HR_EX">Hr_progr_Sal</th> --}}
                                    <th class="tc" v-if="vData.tReport == vData.REP_HR_EX">Tiempo retardo (min)</th>
                                    <th class="tc" v-if="vData.tReport == vData.REP_HR_EX">Salida anticipada (min)</th>
                                    <th class="tc" v-if="vData.tReport == vData.REP_HR_EX">Prima Dominical</th>
                                    <th class="tc" v-if="vData.tReport == vData.REP_HR_EX">Descanso</th>
                                    <th v-if="vData.tReport == vData.REP_HR_EX">Observaciones</th>
                                    <th>Incidencias</th>
                                    <th>Ajustes</th>
                                    <th>id_externo</th>
                                    <th>-</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, index) in vData.lRows" :class="getCssClass(row, vData.tReport)">
                                    <td>@{{ vueGui.pad(row.numEmployee, 6) }}</td>
                                    <td>@{{ row.employee }}</td>
                                    {{-- <td>@{{ row.inDate }}</td> --}}
                                    <td :class="getDtCellCss(row, 1)">@{{ vueGui.formatDateTime(row.inDateTime) }}</td>
                                    {{-- <td>@{{ row.outDate }}</td> --}}
                                    <td :class="getDtCellCss(row, 2)">@{{ vueGui.formatDateTime(row.outDateTime) }}</td>
                                    <td>@{{ row.scheduleText }}</td>
                                    {{-- <td v-if="vData.tReport == vData.REP_DELAY">@{{ row.delayMins }}</td>
                                    <td v-else>@{{ row.extraHours }}</td> --}}
                                    <td>@{{ row.overMinsTotal < 0 ? null : row.overMinsTotal }}</td>{{-- oculta --}}
                                    <td class="tc">@{{ vueGui.formatMinsToHHmm(row.overDefaultMins < 0 || row.overDefaultMins == null ? 0 : row.overDefaultMins) }}</td>
                                    <td class="tc">@{{ vueGui.formatMinsToHHmm(row.overScheduleMins + row.overWorkedMins < 0 || row.overScheduleMins + row.overWorkedMins == null ? 0 : row.overScheduleMins + row.overWorkedMins) }}</td>
                                    <td class="tc">@{{ vueGui.formatMinsToHHmm(row.overMinsByAdjs == null ? 0 : row.overMinsByAdjs) }}</td>
                                    <td class="tc">@{{ vueGui.formatMinsToHHmm(row.overMinsTotal < 0 ? 0 : row.overMinsTotal) }}</td>
                                    {{-- <td v-if="vData.tReport == vData.REP_HR_EX">@{{ row.outDateTimeSch }}</td> --}}
                                    <td class="tc" v-if="vData.tReport == vData.REP_HR_EX">@{{ row.entryDelayMinutes }}</td>
                                    <td class="tc" v-if="vData.tReport == vData.REP_HR_EX">@{{ row.prematureOut }}</td>
                                    <td class="tc" v-if="vData.tReport == vData.REP_HR_EX">@{{ row.isSunday > 0 ? row.isSunday : "" }}</td>
                                    <td class="tc" v-if="vData.tReport == vData.REP_HR_EX">@{{ row.isDayOff > 0 ? row.isDayOff : "" }}</td>
                                    <td v-if="vData.tReport == vData.REP_HR_EX">@{{ row.others }}</td>
                                    <td>@{{ row.comments }}</td>
                                    <td>
                                        @if ($bModify)
                                            <button title="Modificar prenómina" class="btn btn-primary btn-xs" v-on:click="showModal(row, index, false)">
                                                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                                            </button>
                                            <button title="Comentarios prenómina" class="btn btn-info btn-xs" v-on:click="showModal(row, index, true)">
                                                <span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
                                            </button>
                                        @endif
                                        <p>@{{ getAdjToRow(row, index) }}</p>
                                    </td>
                                    <td>@{{ row.external_id }}</td>
                                    <td>@{{ row.idEmployee }}</td>
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
                                            <form action="{{route('reporteIncidenciasEmpleadosGenerar')}}"  autocomplete="off">
                                                <input type="hidden" name="start_date" value={{ $sStartDate }} >
                                                <input type="hidden" name="end_date" value={{ $sEndDate }} >
                                                <input type="hidden" name="emp_id" value="0" >
                                                <input type="hidden" name="report_mode" value="3" >
                                                <input type="hidden" name="delegation" value="{{ $bDelegation }}" >
                                                <input type="hidden" name="id_delegation" value="{{ $idDelegation }}" >
                                                <input type="hidden" name="pay_way" value={{ $pay_way }} >
                                                <input type="hidden" name="wizard" value={{ $wizard }} >
                                                <button type="submit" class="btn btn-primary" id="guardar" >Anterior</button>
                                            </form>
                                        </div>
                                        &nbsp;
                                        <div class="col-md-2" style="font-size:20px;">
                                            &nbsp;<span class="label label-default"> 1 </span> 
                                            &nbsp;<span class="label label-primary"> 2 </span> 
                                            &nbsp;<span class="label label-default"> 3 </span>&nbsp;    
                                        </div>        
                                        <div class="col-md-1">
                                            <form action="{{route('reportetiemposextra')}}"  autocomplete="off">
                                                <input type="hidden" name="start_date" value={{ $sStartDate }} >
                                                <input type="hidden" name="end_date" value={{ $sEndDate }} >
                                                <input type="hidden" name="emp_id" value="0" >
                                                <input type="hidden" name="report_mode" value="3" >
                                                <input type="hidden" name="delegation" value="{{ $bDelegation }}" >
                                                <input type="hidden" name="id_delegation" value="{{ $idDelegation }}" >
                                                <input type="hidden" name="pay_way" value={{ $pay_way }} >
                                                <input type="hidden" name="wizard" value={{ $wizard }} >
                                                <button type="submit" class="btn btn-primary" id="guardar">Siguiente</button>
                                            </form>
                                        </div>
                                    </div>
                                </p>
                                <p>
                                    <div class="row">
                                        <div class="col-md-2"><a href="{{ route('inicio') }}" class="btn btn-danger">Cancelar</a></div>
                                    </div>
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
            this.lEmpWrkdDays = <?php echo json_encode($lEmpWrkdDays) ?>;
            this.adjTypes = <?php echo json_encode($adjTypes) ?>;
            this.lAdjusts = <?php echo json_encode($lAdjusts) ?>;
            this.lEmpVobos = <?php echo json_encode($lEmpVobos) ?>;
            this.lDeptJobs = <?php echo json_encode($lDeptJobs) ?>;
            this.isPrepayrollInspection = <?php echo json_encode($isPrepayrollInspection) ?>;
            this.tReport = <?php echo json_encode($tReport) ?>;
            this.registriesRoute = <?php echo json_encode($registriesRoute) ?>;
            this.REP_HR_EX = <?php echo json_encode(\SCons::REP_HR_EX) ?>;
            this.REP_DELAY = <?php echo json_encode(\SCons::REP_DELAY) ?>;
            this.ADJ_CONS = <?php echo json_encode(\SCons::PP_TYPES) ?>;
            this.lCommentsAdjsTypes = <?php echo json_encode($lCommentsAdjsTypes) ?>;
            this.startDate = <?php echo json_encode($sStartDate) ?>;
            this.endDate = <?php echo json_encode($sEndDate) ?>;
            this.subEmployees = <?php echo json_encode($subEmployees) ?>;
            this.lUsers = <?php echo json_encode($lUsers) ?>;
            this.routegetDirectEmployees = <?php echo json_encode(route('getDirectEmployees')) ?>;

            // this.startDate = moment(this.startDate).format('DD-MM-YYYY');
            // this.endDate = moment(this.endDate).format('DD-MM-YYYY');

            // this.minsCol = this.tReport == this.REP_DELAY ? 4 : 4;
            this.minsCol = 5;
            this.minsBeforeCol = 11;
            this.minsDelayCol = this.tReport == this.REP_DELAY ? 4 : 10;
            this.sunCol = 12;
            this.dayoffCol = 13;
            this.hiddenColEmId = 18;
            this.hiddenColExId = 17;
            this.hiddenCol = this.tReport == this.REP_DELAY ? 5 : 5;
            this.toExport = this.tReport == this.REP_DELAY ? [0, 1, 2, 3, 4, 6] : [0, 1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12];
        }
        
        var oData = new GlobalData();

        /**
         * Convierte los minutos en entero a formato 00:00
         *
         * @param int time
         * 
         * @return string 00:00
         */
        function convertToHoursMins(time) 
        {
            if (time < 1) {
                return "00:00";
            }

            let hours = Math.floor(time / 60);
            if (hours < 10) {
                hours = "0" + hours;
            }
            let minutes = time % 60;
            if (minutes < 10) {
                minutes = "0" + minutes;
            }

            return "" + hours + ":" + minutes;
        }
    </script>

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
                paging: true,
                scrollX: true,
                fixedHeader: true,
                order: [[0, 'asc']],
                columnDefs: [
                    {
                        targets: [ oData.hiddenCol, oData.hiddenColExId, oData.hiddenColEmId ],
                        visible: false
                    },
                    {
                        targets: [ 0 ],
                        orderData: [ 0, 2, 3 ]
                    }
                ],
                rowGroup: {
                    startRender: null,
                    endRender: function ( rows, group ) {
                        let suns = 0;
                        let daysoff = 0;
                        let minsDelay = 0;
                        let minsBeforeOut = 0;
                        let mins = rows
                                    .data()
                                    .pluck(oData.minsCol)
                                    .reduce( function (a, b) {
                                        a = parseInt(a, 10);
                                        if(isNaN(a)){ a = 0; }                   

                                        b = parseInt(b, 10);
                                        if(isNaN(b)){ b = 0; }

                                        a = a < 0 ? 0 : a;
                                        b = b < 0 ? 0 : b;

                                        return a + b;
                                    }, 0);
                        if (oData.tReport == oData.REP_HR_EX) {
                            minsDelay = rows
                                    .data()
                                    .pluck(oData.minsDelayCol)
                                    .reduce( function (a, b) {
                                        a = parseInt(a, 10);
                                        if(isNaN(a)){ a = 0; }                   

                                        b = parseInt(b, 10);
                                        if(isNaN(b)){ b = 0; }

                                        a = a < 0 ? 0 : a;
                                        b = b < 0 ? 0 : b;

                                        return a + b;
                                    }, 0);
                            minsBeforeOut = rows
                                    .data()
                                    .pluck(oData.minsBeforeCol)
                                    .reduce( function (a, b) {
                                        a = parseInt(a, 10);
                                        if(isNaN(a)){ a = 0; }                   

                                        b = parseInt(b, 10);
                                        if(isNaN(b)){ b = 0; }

                                        a = a < 0 ? 0 : a;
                                        b = b < 0 ? 0 : b;

                                        return a + b;
                                    }, 0);
                            suns = rows
                                        .data()
                                        .pluck(oData.sunCol)
                                        .reduce( function (a, b) {
                                            a = parseInt(a, 10);
                                            if(isNaN(a)){ a = 0; }                   

                                            b = parseInt(b, 10);
                                            if(isNaN(b)){ b = 0; }

                                            a = a < 0 ? 0 : a;
                                            b = b < 0 ? 0 : b;

                                            return a + b;
                                        }, 0);

                            daysoff = rows
                                        .data()
                                        .pluck(oData.dayoffCol)
                                        .reduce( function (a, b) {
                                            a = parseInt(a, 10);
                                            if(isNaN(a)){ a = 0; }                   

                                            b = parseInt(b, 10);
                                            if(isNaN(b)){ b = 0; }

                                            a = a < 0 ? 0 : a;
                                            b = b < 0 ? 0 : b;

                                            return a + b;
                                        }, 0);
                        }
                        
                        let value_to_return = '';

                        if (oData.tReport == oData.REP_DELAY) {
                            value_to_return = group +' (Retardo total = ' + mins + ' mins)';
                        }
                        else {
                            let dayOffTheo = oData.lEmpWrkdDays[parseInt(group, 10)];
                            value_to_return = "TOTAL " + group +': tiempo extra: ' + convertToHoursMins(mins) + 
                                                " / tiempo retardo: " + minsDelay + " min " + 
                                                " / salida anticipada: " + minsBeforeOut  + " min" + 
                                                " / primas dominicales: " + suns + 
                                                " / descansos: " + daysoff + " [" + (dayOffTheo == undefined ? 0 : dayOffTheo) + "]";
                        }

                        let oVobo = oData.isPrepayrollInspection ? oData.lEmpVobos[parseInt(group, 10)] : undefined;

                        let isVobo = oVobo != undefined;

                        value_to_return += 
                                    '<br>' +
                                    ('<label>' + 
                                        oData.lDeptJobs[parseInt(group, 10)] + 
                                    '</label>');

                        if (oData.isPrepayrollInspection) {
                            value_to_return += 
                                '<br>' +
                                '<span class="nobr">';
                            if (!isVobo | (isVobo )) {
                                if (! isVobo || (isVobo && oVobo.is_rejected)) {
                                    value_to_return += '<button onclick="handleChangeCheck(event, ' + parseInt(group, 10) + ', \'vobo\')" title="Aprobar Vobo" class="btn btn-success btn-xs btn3d"><i class="fa fa-thumbs-up"></i></button>';
                                }
                                if (! isVobo || (isVobo && oVobo.is_vobo)) {
                                    value_to_return += '<button onclick="handleChangeCheck(event, ' + parseInt(group, 10) + ', \'rejectM\')" title="Rechazar Vobo" class="btn btn-danger btn-xs btn3d"><i class="fa fa-thumbs-down"></i></button>';
                                }
                            }

                            value_to_return += '</span>';

                            if (isVobo) {
                                value_to_return += '<label>[' + 
                                        (oVobo.is_vobo ? ('Revisado por <b>' + oVobo.user_vobo_name) : ('Rechazado por <b>' + oVobo.user_rejected_name)) + '</b>' +
                                        (oVobo.is_vobo ? ('&nbsp;<span title="Vobo aprobado" style="color: green;" class="fa fa-check fa-lg"></span>&nbsp;') : '') +
                                        (oVobo.is_rejected ? ('&nbsp;<span title="Vobo rechazado" style="color: red;" class="fa fa-times fa-lg"></span>&nbsp;') : '') +
                                        (oVobo.is_rejected && oVobo.comments.length > 0 ? ('<span>(' + oVobo.comments + ')</span>') : '') +
                                    ']</label>';
                            }
                        }

                        return value_to_return;
                    },
                    dataSrc: 0
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
                            extend: 'copy',
                            text: 'Copiar',
                            exportOptions: {
                                columns: oData.toExport
                            }
                        },
                        {
                            extend: 'csv',
                            text: 'CSV',
                            exportOptions: {
                                columns: oData.toExport
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            exportOptions: {
                                columns: oData.toExport
                            }
                        },
                        {
                            extend: 'print',
                            text: 'Imprimir',
                            exportOptions: {
                                columns: oData.toExport
                            }
                        }
                    ]
            });
            
            $('#delays_table_filter').prepend('<label for="filtro_horario">Horario: </label>' +
                        '<select id="filtro_horario" class="select2-class" style="width: 15%">' +
                        '</select>' +
                        '&nbsp'
            );
            
            $('#delays_table_filter').prepend('<label for="directos">Empleados: </label>' +
                        '<select id="directos" class="select2-class" style="width: 15%">' +
                            '<option value="0">Todos</option>' +
                            '<option value="1">Directos</option>' +
                        '</select>' +
                        '&nbsp'
            );
            
            $('#delays_table_filter').prepend('<label for="directos">Practicantes: </label>' +
                        '<select class="select2-class" name="sel-collaborator" id="sel-collaborator" style="width: 15%">' +
                            '<option value="0" selected>Todos</option>' +
                            '<option value="1">Empleados</option>' +
                            '<option value="2">Practicantes</option>' +
                        '</select>' +
                        '&nbsp'
            );

            $.fn.dataTable.ext.search.push(
                function( settings, data, dataIndex ) {
                    // var min = parseInt( $('#min').val(), 10 );
                    let collaboratorVal = parseInt( $('#sel-collaborator').val(), 10 );
                    let externalId = 0;

                    switch (collaboratorVal) {
                        case 0:
                            return true;

                        case 1:
                            externalId = parseInt( data[17] );
                            return externalId > 0;

                        case 2:
                            externalId = parseInt( data[17] );
                            return ! (externalId > 0);

                        default:
                            break;
                    }

                    return false;
                }
            );

            $('#sel-collaborator').change( function() {
                oTable.draw();
            });
    </script>

    <script>
        //Get the button:
        reloadButton = document.getElementById("reloadBtn");
        mybutton = document.getElementById("myBtn");
        theNewButton = document.getElementById("newButton");

        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
            if (reloadButton == null || mybutton == null || theNewButton == null) {
                return;
            }

            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                reloadButton.style.display = "block";
                mybutton.style.display = "block";
                theNewButton.style.display = "block";
            } else {
                reloadButton.style.display = "none";
                mybutton.style.display = "none";
                theNewButton.style.display = "none";
            }
        }

        // When the user clicks on the button, scroll to the top of the document
        function topFunction() {
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        }

        function reloadFunction() {
            oGui.showLoading(6000);
            location.reload();
        }

        function getRowsInNumEmployee(numEmployee){
            var data = app.vData.lRows;
            var dataEmployee = [];

            for (let i = 0; i < data.length; i++) {
                if(data[i]['numEmployee'] == numEmployee){
                    dataEmployee.push(data[i]);
                }
            }

            return dataEmployee;
        }

        function checkAdjust(dataEmployee){
            for (let i = 0; i < dataEmployee.length; i++) {
                if(dataEmployee[i]['isDayChecked'] == true && dataEmployee[i]['adjusts'].length < 1){
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
            handleChangeCheck(null, iNumEmployee, 'reject');
        });

        /**
         * 
         * 
         * @param {*} event 
         * @param {*} id 
         */
        function handleChangeCheck(event, numEmployee, sOperation) {
            var dataEmployee = getRowsInNumEmployee(numEmployee);
            let url = "{{ route('employee_vobo') }}";
            var result = sOperation == 'vobo' ? checkAdjust(dataEmployee) : new Array(true);
            if (sOperation == 'rejectM') {
                // show modal
                iNumEmployee = numEmployee;
                $('#rejectReason').val('');
                $('#rejectModalId').modal('show');
                return;
            }

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
                        oData.lEmpVobos = res.data.lvobos;
                        oGui.showMessage(res.data.title, res.data.message, res.data.icon);
                        oTable.draw();
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
    <script>
        $(document).ready(function() {
            $('.select2-class').select2();
            
            $('#filtro_horario').select2({
                placeholder: 'selecciona horario',
                data: app.dataSchedules,
            })
            .on('select2:select', function (e){
                if (e.params.data.id != 'NA') {
                    oTable.columns( 4 ).search( e.params.data.text ).draw();
                }
                else {
                    oTable.columns().search('').draw();
                }
            });

            $('#directos').on('select2:select', function (e){
                if (e.params.data.id == 1) {
                    var searchValues = "";
                    for (let i = 0; i < oData.subEmployees.length; i++) {
                        searchValues = searchValues + '^' + oData.subEmployees[i] + '$' + (i < (oData.subEmployees.length-1) ? "|" : "");
                    }
                    console.log(searchValues, oData.subEmployees);
                    oTable.column(18).search("(" + searchValues + ")", true, false).draw();
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
        });
    </script>
    @if($isAdmin)
        <script>
            $(document).ready(function() {
                $('#supervisores').on('select2:select', function (e){
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
    <script>
        function closeSugerencia() {
            const elem = document.getElementById("sugerencia");
            elem.parentNode.removeChild(elem);
        }
    </script>
@endsection