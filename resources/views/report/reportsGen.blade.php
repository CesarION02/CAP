@extends("theme.$theme.layout")

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
    <link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
@endsection

@section('title')
    {{ $sTitle }}
@endsection

@section('content')
<div class="row" id="reportDelayAppGen">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $sTitle }}</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:reportetiemposextra"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route(''.$sRoute.'') }}" id="theForm">
                <input type="hidden" name="is_active" id="is_active" value="{{$is_active}}">
                 <div class="box-body" id="reportApp">
                    @if($isAdmin)
                        <div class="row">
                            <div class="col-md-2">
                                Supervisores:
                            </div>
                            <div class="col-md-7 col-md-offset-1">
                                <select name="superviser" form="theForm" id="superviser" class="form-control chosen-select" data-placeholder="Selecciona supervisor...">
                                    <option v-for="superviser in lSuperviser" :value="superviser.id">@{{superviser.name}}</option>
                                </select>
                            </div>
                        </div>
                        <br>
                    @endif
                    <div class="row">
                        <div class="col-md-2 requerido">
                            Filtrar por:*
                        </div>
                        @if($is_active == 0)
                            <div class="col-md-3 col-md-offset-1">
                                <label><input v-model="picked" v-on:change="onFilterTypeChange()" type="radio" name="optradio" value="period" onclick="chosDisable();">Periodicidad de pago</label>
                            </div>
                        @else
                            <div class="col-md-3 col-md-offset-1">
                                <label><input disabled v-model="picked" v-on:change="onFilterTypeChange()" type="radio" name="optradio" value="period" onclick="chosDisable();">Periodicidad de pago</label>
                            </div>
                        @endif
                        <div class="col-md-3">
                            <label><input v-model="picked" v-on:change="onFilterTypeChange()" type="radio" name="optradio" value="employee" onclick="chosEnable();">Empleado</label>
                        </div>
                    </div>
                    <br>
                    <div>
                        <div class="row">
                            <div class="col-md-2">
                                Periodicidad de pago:*
                            </div>
                            <div class="col-md-3 col-md-offset-1">
                                <input type="radio" id="option2" value="2" v-model="iPayWay" :disabled="picked == 'employee'" name="pay_way">
                                <label for="option2">Semana</label>
                            </div>

                            <div class="col-md-3">
                                <input type="radio" id="option1" value="1" v-model="iPayWay" :disabled="picked == 'employee'" name="pay_way">
                                <label for="option1">Quincena</label>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div>
                        <div class="row">
                            <div class="col-md-2">
                                Empleado:*
                            </div>
                            <div class="col-md-7 col-md-offset-1">
                                <select :disabled="picked == 'period'" v-model='idEmployee' name="emp_id" form="theForm" id="emp_id" class="form-control chosen-select" data-placeholder="Selecciona empleado...">
                                    <option v-for="employee in lEmps" :value="employee.id">@{{ employee.name }} - @{{ employee.num_employee }}</option>
                                </select>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            Filtrar:*
                        </div>
                        <div class="col-md-7 col-md-offset-1">
                            @include('filters.adept')
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-2 requerido">
                            Quitar empleados que no checan:
                        </div>
                        <div class="col-md-3 col-md-offset-1">
                            <input id="cbx1" type="checkbox" name="nochecan" value="nochecan" checked>
                        </div>
                    </div>
                    <br>
                    <br>
                    <div class="row">
                        <div class="col-md-2">
                            Fecha inicial:*
                            <br>
                            Fecha final:*
                        </div>
                        <div class="col-md-4 col-md-offset-1">
                            {{-- <input type="hidden" id="start-date" name="start_date">
                            <input type="hidden" id="end-date" name="end_date"> --}}
                            <div class="input-group">
                                @include('controls.b-week', ['start_date_v' => null, 'end_date_v' => null,
                                                        'start_date_name' => 'start_date', 'end_date_name' => 'end_date'])
                            </div>
                        </div>
                        {{-- <input :value="startDate" type="hidden" name="start_date">
                        <input :value="endDate" type="hidden" name="end_date"> --}}
                        <div class="col-md-4">
                            <label>Código de colores:</label>
                            <br>
                            <span class="label delays">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span> (Con retardos)
                            <br>
                            <span class="label check">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span> (Turno u horario a revisar)
                            <br>
                            <span class="label absence">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span> (Con faltas o sin checadas)
                            <br>
                            <span class="label noprogramming">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span> (Sin checadas y sin horario)
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-2">
                            Tipo de reporte:*
                        </div>
                        <div class="col-md-7 col-md-offset-1">
                        <select name="report_mode" id="report_mode" class="form-control">
                            <option value="2">Detalle</option>
                            <option value="3">Resumen</option>
                        </select>
                        </div>
                    </div>
                    <br>
                </div>
                <div class="box-footer">
                    <div class="col-lg-4 col-lg-offset-3">
                        <p style="color:red">Nota: Este proceso podría demorar algunos minutos</p>
                    </div>
                    <div class="col-lg-2">
                        <button class="btn btn-warning" id="generar" name="generar" type="submit" onclick="this.form.submit(); this.disabled=true; oGui.showLoading(60000); ">Generar</button>
                    </div>
                </div>
                <br>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script>
        function chosEnable(){
            $('#emp_id').prop('disabled', false).trigger("chosen:updated");
        }
        function chosDisable(){
            $('#emp_id').prop('disabled', true).trigger("chosen:updated");
        }
    </script>

    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    
    <script>
            function GlobalData () {
                this.aData = <?php echo json_encode(\App\SUtils\SGuiUtils::getAreasAndDepts()) ?>;
                this.startOfWeek = <?php echo json_encode($startOfWeek) ?>;
                this.lEmployees = <?php echo json_encode($lEmployees) ?>;
                this.lAreas = this.aData[0];
                this.lDepts = this.aData[1];
                this.picked = <?php echo json_encode($radioB) ?>;
                this.lSuperviser = <?php echo json_encode($lSuperviser) ?>;
            }
            var oGui = new SGui();
            var oData = new GlobalData();
        </script>
    <script src="{{ asset("assets/pages/scripts/filters/SFilter.js") }}" type="text/javascript"></script>

    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    
    <script src="{{ asset("assets/pages/scripts/report/SDelayReportGen.js") }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(function() {

        
            var start = moment().subtract(6, 'days');
            var end = moment();

            let startWeek = moment().day(oData.startOfWeek);
            let endWeek = moment().day(oData.startOfWeek + 6);

            let startLastWeek = moment().day(oData.startOfWeek - 7);
            let endLastWeek = moment().day(oData.startOfWeek - 1);

            let startThisFortnight = null;
            let endThisFortnight = null;
            let startLastFortnight = null;
            let endLastFortnight = null;
        
            if (moment().date() > 15) {
                startThisFortnight = moment().date(16);
                endThisFortnight = moment().endOf('month');
                startLastFortnight = moment().startOf('month');
                endLastFortnight = moment().date(15);
            }
            else {
                startThisFortnight = moment().startOf('month');
                endThisFortnight = moment().date(15);
                startLastFortnight = moment().date(16);
                endLastFortnight = moment().endOf('month');
            }
        
            function cb(start, end) {
                $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
                app.setDates(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            }
        
            $('#reportrange').daterangepicker({
                startDate: startWeek,
                endDate: endWeek,
                ranges: {
                   'Hoy': [moment(), moment()],
                   'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'Semana actual': [startWeek, endWeek],
                   'Semana pasada': [startLastWeek, endLastWeek],
                   'Quincena actual': [startThisFortnight, endThisFortnight],
                   'Quincena pasada': [startLastFortnight, endLastFortnight],
                   'Mes actual': [moment().startOf('month'), moment().endOf('month')],
                   'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);
        
            cb(startWeek, endWeek);
        
        });

        $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
            app.setDates(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
        });
    </script>

    <script>
        $(document).ready(function() {
            $("#cbx1").click(function() {
                if ($(this).is(":checked")){
                  doChecked(); // Función si se checkea
                } else {
                  doNotChecked(); //Función si no
                }
            });
         });
    </script>
    <script>
        $(".chosen-select").chosen();
    </script>
@endsection
