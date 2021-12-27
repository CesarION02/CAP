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
                            <option value="2">Practicantes</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('generarreportetiemposextra') }}" target="_blank" class="btn btn-success">Nuevo reporte</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed" style="width:100%">
                        <thead>
                                <tr>
                                    <th>Num. Col.</th>
                                    <th>Empleado</th>
                                    <th>Total Tiempo retardo (min)</th>
                                    <th>Salidas anticipadas (min)</th>
                                    <th>Total Tiempo extra (hr)</th>
                                    <th>Primas Dominicales</th>
                                    <th>Descansos</th>
                                    <th>Faltas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in vData.lEmployees">
                                <td>@{{ vueGui.pad(row.num_employee, 6) }}</td>
                                    <td>@{{ row.name }}</td>
                                    <td>@{{ row.entryDelayMinutes }}</td>
                                    <td>@{{ row.prematureOut }}</td>
                                    <td>@{{ row.extraHours }}</td>
                                    <td>@{{ row.isSunday }}</td>
                                    <td>@{{ row.isDayOff }}</td>
                                    <td>@{{ row.hasAbsence }}</td>
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
        this.lEmployees = <?php echo json_encode($lEmployees) ?>;
        this.lEmpWrkdDays = <?php echo json_encode($lEmpWrkdDays) ?>;
        this.adjTypes = <?php echo json_encode($adjTypes) ?>;
        this.lAdjusts = <?php echo json_encode($lAdjusts) ?>;
        this.tReport = <?php echo json_encode($tReport) ?>;
        this.registriesRoute = <?php echo json_encode($registriesRoute) ?>;
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

        for(let i = 0; i<this.lEmployees.length; i++){
            this.lEmployees[i].extraHours = convertToHoursMins(this.lEmployees[i].extraHours);
        }
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