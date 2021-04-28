@extends("theme.$theme.layoutcustom")
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
            <div class="box-header with-border">
                <h3 class="box-title">{{ $sTitle }}</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body" id="reportDelayApp">
                @include('report.adjustsModal')
                <div class="row">
                    <div class="col-md-6">
                        <p>Periodo: <b>{{ $sStartDate }}</b> - <b>{{ $sEndDate }}</b>. P. pago: <b>{{ $sPayWay }}</b>.</p>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" name="sel-collaborator" id="sel-collaborator">
                            <option value="0" selected>Todos</option>
                            <option value="1">Empleados</option>
                            <option value="2">Becarios</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('generarreportetiemposextra') }}" target="_blank" class="btn btn-success">Nuevo reporte</a>
                    </div>
                    <div class="col-md-2">
                        <div id="wrapper">
                            <button class="btn btn-info" id="button-a">Crear Excel</button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Num. Col.</th>
                                    <th>Colaborador</th>
                                    {{-- <th>Fecha entrada</th> --}}
                                    <th><span class="nobr">Fecha-hora</span> entrada</th>
                                    {{-- <th>Fecha salida</th> --}}
                                    <th><span class="nobr">Fecha-hora</span> salida</th>
                                    {{-- <th v-if="vData.tReport == vData.REP_DELAY">Retardo (min)</th>
                                    <th v-else>Horas Extra</th> --}}
                                    <th>Horario</th>
                                    <th>Tiempo retardo (min)</th>
                                    <th>Tiempo extra (hr)</th>
                                    {{-- <th v-if="vData.tReport == vData.REP_HR_EX">Hr_progr_Sal</th> --}}
                                    <th v-if="vData.tReport == vData.REP_HR_EX">Tiempo retardo (min)</th>
                                    <th v-if="vData.tReport == vData.REP_HR_EX">Salida anticipada (min)</th>
                                    <th v-if="vData.tReport == vData.REP_HR_EX">Prima Dominical</th>
                                    <th v-if="vData.tReport == vData.REP_HR_EX">Descanso</th>
                                    <th v-if="vData.tReport == vData.REP_HR_EX">Observaciones</th>
                                    <th>Incidencias</th>
                                    <th>Ajustes</th>
                                    <th>id_externo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in vData.lRows" :class="getCssClass(row, vData.tReport)">
                                    <td>@{{ vueGui.pad(row.numEmployee, 6) }}</td>
                                    <td>@{{ row.employee }}</td>
                                    {{-- <td>@{{ row.inDate }}</td> --}}
                                    <td>@{{ vueGui.formatDateTime(row.inDateTime) }}</td>
                                    {{-- <td>@{{ row.outDate }}</td> --}}
                                    <td>@{{ vueGui.formatDateTime(row.outDateTime) }}</td>
                                    <td>@{{ row.scheduleText }}</td>
                                    {{-- <td v-if="vData.tReport == vData.REP_DELAY">@{{ row.delayMins }}</td>
                                    <td v-else>@{{ row.extraHours }}</td> --}}
                                    <td>@{{ row.overMinsTotal < 0 ? null : row.overMinsTotal }}</td>
                                    <td>@{{ vueGui.formatMinsToHHmm(row.overMinsTotal < 0 ? 0 : row.overMinsTotal) }}</td>
                                    {{-- <td v-if="vData.tReport == vData.REP_HR_EX">@{{ row.outDateTimeSch }}</td> --}}
                                    <td v-if="vData.tReport == vData.REP_HR_EX">@{{ row.entryDelayMinutes }}</td>
                                    <td v-if="vData.tReport == vData.REP_HR_EX">@{{ row.prematureOut }}</td>
                                    <td v-if="vData.tReport == vData.REP_HR_EX">@{{ row.isSunday > 0 ? row.isSunday : "" }}</td>
                                    <td v-if="vData.tReport == vData.REP_HR_EX">@{{ row.isDayOff > 0 ? row.isDayOff : "" }}</td>
                                    <td v-if="vData.tReport == vData.REP_HR_EX">@{{ row.others }}</td>
                                    <td>@{{ row.comments }}</td>
                                    <td>
                                        <button class="btn btn-primary btn-xs" v-on:click="showModal(row)">
                                            <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
                                        </button>
                                        <p>@{{ getAdjToRow(row) }}</p>
                                    </td>
                                    <td>@{{ row.external_id }}</td>
                                </tr>
                            </tbody>
                            <button onclick="topFunction()" id="myBtn" title="Ir arriba">Ir arriba</button>
                            <a href="{{ route('generarreportetiemposextra') }}" target="_blank" id="newButton" title="Nuevo reporte">Nuevo reporte</a>
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
        function GlobalData () {

            this.lRows = <?php echo json_encode($lRows) ?>;
            this.lEmpWrkdDays = <?php echo json_encode($lEmpWrkdDays) ?>;
            this.adjTypes = <?php echo json_encode($adjTypes) ?>;
            this.lAdjusts = <?php echo json_encode($lAdjusts) ?>;
            this.tReport = <?php echo json_encode($tReport) ?>;
            this.REP_HR_EX = <?php echo json_encode(\SCons::REP_HR_EX) ?>;
            this.REP_DELAY = <?php echo json_encode(\SCons::REP_DELAY) ?>;
            this.ADJ_CONS = <?php echo json_encode(\SCons::PP_TYPES) ?>;

            // this.minsCol = this.tReport == this.REP_DELAY ? 4 : 4;
            this.minsCol = 5;
            this.minsBeforeCol = 8;
            this.minsDelayCol = this.tReport == this.REP_DELAY ? 4 : 7;
            this.sunCol = 9;
            this.dayoffCol = 10;
            this.hiddenColExId = 14;
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

        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                // var min = parseInt( $('#min').val(), 10 );
                let collaboratorVal = parseInt( $('#sel-collaborator').val(), 10 );
                let externalId = 0;

                switch (collaboratorVal) {
                    case 0:
                        return true;

                    case 1:
                        externalId = parseInt( data[14] );
                        return externalId > 0;

                    case 2:
                        externalId = parseInt( data[14] );
                        return ! (externalId > 0);

                    default:
                        break;
                }

                return false;
            }
        );

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
                fixedHeader: {
                    header: true
                },
                order: [[0, 'asc']],
                columnDefs: [
                    {
                        targets: [ oData.hiddenCol, oData.hiddenColExId ],
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

            $('#sel-collaborator').change( function() {
                oTable.draw();
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

    <script src="{{asset("assets/pages/scripts/report/SReportRow.js")}}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/report/SExportExcel.js")}}" type="text/javascript"></script>
@endsection