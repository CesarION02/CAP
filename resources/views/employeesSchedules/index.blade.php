@extends("theme.$theme.layout")
@section('title')
    Horarios de empleados
@endsection

@section('content')
<div class="row" id="employeesSchedulesApp">
    @include('employeesSchedules.modalScheduleDays')
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Horarios</h3>
                <div class="row">
                    <form action="{{route('empl_schedules')}}">
                        @csrf
                        <div class="col-md-8">
                            <label>Selecciona empleado</label>
                            <select class="select2-class" id="select_employees" name="selEmployee">
                                <option value=""></option>
                                @foreach ($lEmployees as $emp)
                                    @if ($oEmployee->employee_id == $emp->employee_id)
                                        <option value="{{$emp->employee_id}}" selected>{{$emp->employee}}</option>
                                    @else
                                        <option value="{{$emp->employee_id}}">{{$emp->employee}}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">Consultar horarios</button>
                        </div>
                    </form>
                    <button class="btn btn-success" v-on:click="programarNuevo();">Nuevo horario fijo</button>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="schedulesTable">
                    <thead>
                        <tr>
                            <th>Num empleado</th>
                            <th>Empleado</th>
                            <th>Departamento</th>
                            <th>Horario</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>-</th>
                            <th>-</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="schedule in lSchedules">
                            <td>@{{oEmployee.num_employee}}</td>
                            <td>@{{oEmployee.employee_name}}</td>
                            <td>@{{oEmployee.dept}}</td>
                            <td>@{{schedule.name}}</td>
                            <td>@{{schedule.start_date}}</td>
                            <td>@{{schedule.end_date != null ? schedule.end_date : 'Indefinido'}}</td>
                            <td><button type="button" class="btn btn-info" v-on:click="showModalDays(schedule.days);"><span class="glyphicon glyphicon-calendar"></span></button></td>
                            <td><button type="button" class="btn btn-danger" v-on:click="deleteSchedule(schedule.schedule_id, schedule.start_date, (schedule.end_date != null ? schedule.end_date : 'Indefinido'));"><span class="glyphicon glyphicon-trash"></span></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>

    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.print.min.js') }}"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script>
    $(document).ready( function () {
        $('#schedulesTable').DataTable({
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
            "order": [[ 4, 'desc' ]],
            "colReorder": true,
            "dom": 'Bfrtip',
            "lengthMenu": [
                [ 10, 25, 50, 100, -1 ],
                [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
            ],
            "buttons": [
                'pageLength',
                { extend: 'copy', text: 'Copiar'}, 'csv', 'excel', { extend: 'print', text: 'Imprimir'}
                ]
        });
        $('.select2-class').select2();
    });
    </script>
    <script>
        function GlobalData () {
            this.lEmployees = <?php echo json_encode($lEmployees) ?>;
            this.oEmployee = <?php echo json_encode($oEmployee) ?>;
            this.lSchedules = <?php echo json_encode($lSchedules) ?>;
            this.routeDelSchedules = <?php echo json_encode(route('empl_schedules_delete', ':id')) ?>;
            this.routeProgramming = <?php echo json_encode(route('programar', ':id')) ?>;
        }
        var oData = new GlobalData();
        var oGui = new SGui();
    </script>
    <script src="{{asset("assets/pages/scripts/employeesSchedules/employeesSchedules.js")}}" type="text/javascript"></script>    
@endsection