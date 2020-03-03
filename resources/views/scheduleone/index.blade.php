@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
@endsection
@section('title')
Guardias Sabatinas
@endsection


@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box" id="assingOneApp">
            <div class="box-header with-border">
                <h3 class="box-title">Guardias Sabatinas</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-info" v-on:click="refresh()">
                        <i class="glyphicon glyphicon-refresh"></i>
                    </button>
                    <button type="button" class="btn btn-success" v-on:click="onShowModal()">
                        <i class="fa fa-fw fa-plus-circle"></i> Nueva guardia
                    </button>
                </div>
            </div>
            <div class="box-body">
                <table id="schedules_table" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Empleado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="schedule in vueServerData.lSchedules">
                            <td>@{{ schedule.start_date }}</td>
                            <td>@{{ schedule.name }}</td>
                            <td>
                                <button v-on:click="onShowEditModal(schedule)" 
                                        class="btn-accion-tabla tooltipsC" title="Editar este registro">
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
    <script>
            function ServerData () {
                this.lSchedules = <?php echo json_encode($lSchedules) ?>;
                this.lEmployees = <?php echo json_encode($lEmployees) ?>;
                this.iTemplateId = <?php echo json_encode($iTemplateId) ?>;
                this.iGrpSchId = <?php echo json_encode($iGrpSchId) ?>;
            }
            
            var oServerData = new ServerData();
            var oGui = new SGui();
    </script>
    <script src="{{ asset("assets/pages/scripts/assign/SAssignament.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/assign/VueAssignOne.js") }}" type="text/javascript"></script>
    <script>
        $(".chosen-select").chosen({width: "98%"});
    </script>
    <script>
        $(document).ready(function() {
                let table = $('#schedules_table').DataTable({
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
                            'pageLength', 'copy', 'csv', 'excel', 'print'
                        ]
                });

                // setInterval( function () {
                //     table.ajax.reload();
                // }, 60000 );
            });
    </script>
@endsection