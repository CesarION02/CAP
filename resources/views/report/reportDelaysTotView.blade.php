@extends("theme.$theme.layoutcustom")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
    <link href="{{ asset("select2js/css/select2.min.css") }}" rel="stylesheet" />
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
<div class="row" id="reportDelayApp">

<!-- Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentsModalLabel">@{{nameEmployee}}</h5>
            </div>
            <div class="modal-body">
                <ol>
                    <li v-for="com in resumeComments">@{{com}}</li>
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

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
                                    <th>Num. Col.</th>
                                    <th>Empleado</th>
                                    <th></th>
                                    <th>Horario</th>
                                    <th>Total Tiempo retardo (min)</th>
                                    <th>Salidas anticipadas (min)</th>
                                    <th>Total Tiempo extra (hr)</th>
                                    <th>Primas Dominicales</th>
                                    <th>Descansos</th>
                                    <th>Faltas</th>
                                    <th>-</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in vData.lEmployees">
                                <td>@{{ vueGui.pad(row.num_employee, 6) }}</td>
                                    <td>
                                        <form action="{{route('reportetiemposextra')}}" target="_blank">
                                            <a href='#' onclick='this.parentNode.submit(); return false;' title="Presiona para ver reporte a detalle">@{{ row.name }}</a>
                                            <input type="hidden" name="start_date" value="{{$sStartDate}}">
                                            <input type="hidden" name="end_date" value="{{$sEndDate}}">
                                            <input type="hidden" name="emp_id" :value="row.id">
                                            <input type="hidden" name="report_mode" value="2">
                                            <input type="hidden" name="optradio" value="employee">
                                            <input type="hidden" name="pay_way" value="{{$sPayWay}}">
                                        </form>
                                    </td>
                                    <td><button type="button" href="#" class="btn btn-primary btn-xs" v-on:click="getResumeComments(row.id)" title="Ver comentarios"><span class="glyphicon glyphicon-list-alt"></span></button></td>
                                    <td>@{{ row.scheduleText }}</td>
                                    <td>@{{ row.entryDelayMinutes }}</td>
                                    <td>@{{ row.prematureOut }}</td>
                                    <td>@{{ row.extraHours }}</td>
                                    <td>@{{ row.isSunday }}</td>
                                    <td>@{{ row.isDayOff }}</td>
                                    <td>@{{ row.hasAbsence }}</td>
                                    <td>@{{ row.id }}</td>
                                </tr>
                            </tbody>
                            @if( isset($wizard) )
                                @if( $wizard != 2 )
                                    <button onclick="topFunction()" id="myBtn" title="Ir arriba">Ir arriba</button>
                                    <a href="{{ route('generarreportetiemposextra') }}" target="_blank" id="newButton" title="Nuevo reporte">Nuevo reporte</a>
                                @endif
                            @else
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
                                                <input type="hidden" name="wizard" value={{ $wizard }} >
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
                                    <div class="row">
                                        <div class="col-md-1"><a href="{{ route('inicio') }}" class="btn btn-danger">Cancelar</a></div>
                                        @if( $pay_way == 2 )
                                            <div class="col-md-1"><a href="{{ route('vobos',['id'=> 'week']) }}" class="btn btn-primary">Terminar proceso</a></div>
                                        @else
                                            <div class="col-md-1"><a href="{{ route('vobos',['id'=> 'biweek']) }}" class="btn btn-primary">Terminar proceso</a></div>
                                        @endif
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
<script src="{{ asset('select2js/js/select2.min.js') }}"></script>

<script>
    function GlobalData () {

        this.lRows = <?php echo json_encode($lRows) ?>;
        this.lComments = <?php echo json_encode($lComments) ?>;
        this.lEmployees = <?php echo json_encode($lEmployees) ?>;
        this.lEmpWrkdDays = <?php echo json_encode($lEmpWrkdDays) ?>;
        this.adjTypes = <?php echo json_encode($adjTypes) ?>;
        this.lAdjusts = <?php echo json_encode($lAdjusts) ?>;
        this.tReport = <?php echo json_encode($tReport) ?>;
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
        this.hiddenColEmId = 10;
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
                        targets: [ oData.hiddenColEmId ],
                        visible: false
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

        $('#delays_table_filter').prepend('<label for="filtro_horario">Horario: </label>' +
                    '<select id="filtro_horario" class="select2-class" style="width: 20%">' +
                    '</select>' +
                    '&nbsp'
        );
        
        $('#delays_table_filter').prepend('<label for="directos">Empleados: </label>' +
                    '<select id="directos" class="select2-class" style="width: 10%">' +
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
            
            $('#filtro_horario').select2({
                placeholder: 'selecciona horario',
                data: app.dataSchedules,
            })
            .on('select2:select', function (e){
                if (e.params.data.id != 'NA') {
                    oTable.columns(3).search( e.params.data.text ).draw();
                }
                else {
                    oTable.columns().search('').draw();
                }
            });

            $('#directos').on('select2:select', function (e) {
                if (e.params.data.id == 1) {
                    var searchValues = "";
                    for (let i = 0; i < oData.subEmployees.length; i++) {
                        searchValues = searchValues + '^' + oData.subEmployees[i] + '$' + (i < (oData.subEmployees.length-1) ? "|" : "");
                    }
                    console.log(searchValues, oData.subEmployees);
                    oTable.column(10).search("(" + searchValues + ")", true, false).draw();
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