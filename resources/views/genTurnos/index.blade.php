@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link href="https://rawgit.com/select2/select2/master/dist/css/select2.min.css" rel="stylesheet"/>
@endsection
@section('title')
Generación de checadas
@endsection

@section('content')
<div class="row" id="genCheck">
    <div class="col-md-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Generación espontanea de registros</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:generacionespontanea"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <form id="form" action="{{route('registro_generate_generate')}}" method="POST">
                @csrf
                <div class="box-body" id="reportApp">
                    <div class="row">
                    <div class="row">
                        <div class="col-md-7 col-md-offset-1">
                            <label for="" style="float: left;">Elige rango de fecha: </label>
                            <div class="input-group">
                                <input type="hidden" id="initDate" name="initDate">
                                <input type="hidden" id="finDate" name="finDate">
                                <div id="daterange-b-week" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-1" id='selectemp'>
                            <label for="employees">Elige empleado:</label>
                            <select id="selectEmployees" style="width: 350px;" name="employee" required></select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-4 col-md-offset-1">
                            <label for="tipo">Turnos:</label>
                            <select id="selectWorkships" style="width: 350px;" name="workships[]" multiple="multiple" required></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-check-label" for="extraHours">
                              Horas extra nocturnas:
                            </label>
                            <input class="form-check-input" type="checkbox" value="" id="extraHours" v-model="extraHours" name="extraHours">
                            <span>(@{{extraHours == true ? 'Sí' : 'No'}})</span>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-1">
                            <label for="descanso">Descanso:</label>
                            <select id="descanso" style="width: 350px;" name="descanso" required></select>
                        </div>
                    </div>
                    <br>
                </div>
            </form>
            <table class="table table-striped table-bordered table-hover" id="myTable">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background-color: rgb(172, 172, 172)">
                        <th>Empleado:</th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr v-for="emp in employee">
                        <td>@{{emp.text}}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr style="background-color: rgb(172, 172, 172)">
                        <th>Turnos:</th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr v-for="wrk in workship">
                        <td>@{{wrk.text}}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr style="background-color: rgb(172, 172, 172)">
                        <th>Fecha incio</th>
                        <th>Fecha fin</th>
                        <th>Total semanas</th>
                    </tr>
                    <tr>
                        <td style="text-align: center;">@{{startDate}}</td>
                        <td style="text-align: center;">@{{endDate}}</td>
                        <td style="text-align: center;">@{{weeks}}</td>
                    </tr>
                    <tr style="background-color: rgb(172, 172, 172)">
                        <th>Dia descanso:</th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr v-for="desc in descanso">
                        <td>@{{desc.text}}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <div class="box-footer">
                <div class="" style="float: right;">
                    <button id="generar" class="btn btn-warning" type="submit">Generar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
    
    
    
    
    
    
    
    
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/report/typeReport.js")}}" type="text/javascript"></script>
    <script src="https://rawgit.com/select2/select2/master/dist/js/select2.js"></script>
    <script type="text/javascript">
        $(function() {
            moment.locale('es');
    
            var s = '<?php echo $start_date; ?>';
            var e = '<?php echo $end_date; ?>';
            
            let dateStart = null;
            let dateEnd = null;
            if(s != "" && e != ""){
                dateStart = moment(s + "T00:00:00", 'YYYY-MM-DD');
                dateEnd = moment(e + "T00:00:00", 'YYYY-MM-DD');
            }
    
            let start = dateStart == null ? moment().startOf('week') : dateStart;
            let end = dateEnd == null ? moment().endOf('week') : dateEnd;
    
            var weekCuts = {};
    
            cb(start, end);
    
            function cb(start, end) {
                start = moment(start).startOf('week');
                end = moment(end).endOf('week');

                $('#daterange-b-week span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
    
                let start_d = document.getElementById("initDate");
                let end_d = document.getElementById("finDate");
                    
                start_d.setAttribute('value', start.format('YYYY-MM-DD'));
                end_d.setAttribute('value', end.format('YYYY-MM-DD'));

                var diff = parseInt((end-start)/(24*3600*1000));
                diff = (diff + 1)/7;

                app.setDates(start.format('D MMMM YYYY'), end.format('D MMMM YYYY'), diff);
            }
    
            $('#daterange-b-week').daterangepicker({
                        "showWeekNumbers": true,
                        startDate: start,
                        endDate: end,
                        drops: "auto"
                    }, cb);
        });
    </script>
    <script>
        $(document).ready( function () {
            $.fn.dataTable.moment('DD/MM/YYYY');
            $('#myTable').DataTable({
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
                    }
                },
                "bSort": false,
                "bFilter": false,
                "dom": 'Bfrtip',
                "lengthMenu": [
                    [ 15, 25, 50, 100, -1 ],
                    [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                ],
                "buttons": [
                    'pageLength',
                    { extend: 'copy', text: 'Copiar'}, 'csv', 'excel', { extend: 'print', text: 'Imprimir'}
                ]
            });
        });
    </script>
    <script>
        function GlobalData () {
            this.Employees = <?php echo json_encode($employees) ?>;
            this.Workships = <?php echo json_encode($workships) ?>;
        }
        var oData = new GlobalData();
    </script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vues/genCheck.js") }}" type="text/javascript"></script>
@endsection