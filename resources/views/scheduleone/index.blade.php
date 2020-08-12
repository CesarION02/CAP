@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection
@section('title')
    Guardias sabatinas
@endsection


@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box" id="assingOneApp">
            <div class="box-header with-border">
                <h3 class="box-title">Guardias sabatinas</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:guardiassabatinas"])
                <div class="box-tools pull-right">
                    <div class="row">
                        <div class="col-md-2 col-md-offset-10">
                            {{-- <button type="button" class="btn btn-info" v-on:click="refresh()">
                                <i class="glyphicon glyphicon-refresh"></i>
                            </button> --}}
                            <button type="button" class="btn btn-success" v-on:click="onShowModal()">
                                <i class="fa fa-fw fa-plus-circle"></i> Nuevo
                            </button>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <form action="{{ route('asignar_uno') }}">
                            <input type="hidden" name="start_date" v-model="sStartDate">
                            <input type="hidden" name="end_date" v-model="sEndDate">
                            <div class="col-md-5 col-md-offset-7">
                                <div class="input-group">
                                    <div id="reportrange" 
                                        v-on:change="onShowModal()"
                                        style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit">
                                            <i class="glyphicon glyphicon-search"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="box-body" id="the_box">
                <table id="assigns_id" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Num</th>
                            <th>Empleado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="schedule in vueServerData.lSchedules">
                            <td>@{{ schedule.start_date }}</td>
                            <td>@{{ vueGui.pad(schedule.num_employee, 6) }}</td>
                            <td>@{{ schedule.name }}
                                    <i v-show="schedule.text_description != undefined" 
                                    class="glyphicon glyphicon-info-sign" 
                                    :title="schedule.text_description"></i>
                            </td>
                            <td>
                                <button v-on:click="onShowEditModal(schedule)" 
                                        class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </button>
                                <button v-on:click="prevDeleteAssignament(schedule)" 
                                        class="btn-accion-tabla tooltipsC" title="Borrar este registro">
                                    <i class="glyphicon glyphicon-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @include('scheduleone.modal')
        </div>
    </div>
</div>
@endsection

@section("scripts")
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
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
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    <script>
            function ServerData () {
                this.lSchedules = <?php echo json_encode($lSchedules) ?>;
                this.startDate = <?php echo json_encode($startDate) ?>;
                this.endDate = <?php echo json_encode($endDate) ?>;

                this.lEmployees = <?php echo json_encode($lEmployees) ?>;
                this.holidays = <?php echo json_encode($holidays) ?>;
                this.iTemplateId = <?php echo json_encode($iTemplateId) ?>;
                this.iGrpSchId = <?php echo json_encode($iGrpSchId) ?>;
            }
            
            var oServerData = new ServerData();
            var oGui = new SGui();
    </script>
    <script src="{{ asset("assets/pages/scripts/assign/SAssignament.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/assign/SHolidayAux.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/assign/VueAssignOne.js") }}" type="text/javascript"></script>
    <script type="text/javascript">
        $(function() {

            var start = moment(oServerData.startDate);
            var end = moment(oServerData.endDate);

            function cb(start, end) {
                $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            }

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Último mes': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'A fin de año': [moment(), moment().endOf('year')]
                }
            }, cb);

            cb(start, end);
        });

        $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
            app.setDates(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
        });
    </script>
    <script>
        $(".chosen-select").chosen({width: "98%"});
    </script>
    <script>
        function reloadTable() {
                let table = $('#assigns_id').DataTable({
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
                    "buttons": [
                        { extend: 'copy', text: 'Copiar'}, 'csv', 'excel', { extend: 'print', text: 'Imprimir'}
                        ]
                });

                // setInterval( function () {
                //     table.ajax.reload();
                // }, 60000 );
            }

            reloadTable();
    </script>
@endsection