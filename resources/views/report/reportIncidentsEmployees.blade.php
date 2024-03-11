@extends("theme.$theme.layout")

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
    <link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
@endsection

@section('title')
    Reporte de incidencias empleados
@endsection

@section('content')
<div class="row" id="reportDelayAppGen">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte prenómina</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:reporteincidenciasempleado"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route(''.$sRoute.'') }}" id="theForm">
                <div class="box-body" id="reportApp">
                    <div class="row">
                       
                        <div class="col-md-3 col-md-offset-1">
                            <input type="hidden" name="optradio" value="period">
                        </div>
                        @if( $wizard != 2 )
                            <div class="col-md-3">
                                <label><input v-model="picked" v-on:change="onFilterTypeChange()" type="radio" name="optradio" value="employee" onclick="chosEnable();">Empleado</label>
                            </div>
                        @endif
                    </div>
                    <br>
                    <div>
                        <div class="row">
                            <div class="col-md-2">
                                Periodicidad de pago:*
                            </div>
                            <div class="col-md-4 col-md-offset-1">
                                <select :disabled="picked == 'employee'" name="pay_way" id="pay_way" class="form-control" v-model="iPayWay">
                                    <option value="2">Semana</option>
                                    <option value="1">Quincena</option>
                                    <option value="0">Todos</option>
                                </select>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div>
                        @if( $wizard != 2)
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
                        @endif
                        <br>
                    </div>
                    @if( $wizard != 2)
                        <div class="row">
                            <div class="col-md-2">
                                Filtrar:*
                            </div>
                            <div class="col-md-7 col-md-offset-1">
                                @include('filters.adept')
                            </div>
                        </div>
                    @endif
                    <br>
                    <input type="hidden" id="wizard" name="wizard" value="{{$wizard}}">
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
                                {{-- @include('controls.b-week', ['start_date_v' => null, 'end_date_v' => null,
                                                        'start_date_name' => 'start_date', 'end_date_name' => 'end_date']) --}}
                                    
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-primary active">
                                        <input type="radio" name="options" id="week" value="week" checked> Semana
                                    </label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="options" id="biweek" value="biweek"> Quincena
                                    </label>
                                    <label class="btn btn-primary">
                                        <input type="radio" name="options" id="biweekcal" value="biweekcal"> Quincena Cal.
                                    </label>
                                </div>
                                <input type="hidden" id="start-date" name="start_date">
                                <input type="hidden" id="end-date" name="end_date">
                                <div class="row">
                                    <div class="col-md-4 col-sm-5">
                                        <input class="form-control input-sm" style="text-align: right; border: 2px solid blue;" type="number" name="year" id="year_id">
                                    </div>
                                    <div class="col-md-8 col-sm-7" style="padding: 0px;">
                                        <small style="font-size: 72%" id="helpId" class="form-text text-muted">Seleccione año para cambiar fechas de corte</small>
                                    </div>
                                </div>
                                <div id="daterange-b-week" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                                <small id="helpId" class="form-text text-muted">Seleccione rango de fechas</small>
                            </div>
                        </div>
                        {{-- <input :value="startDate" type="hidden" name="start_date">
                        <input :value="endDate" type="hidden" name="end_date"> --}}
                        <div class="col-md-4">
                            <label>Código de colores:</label>
                            <br>
                            <span class="label" style="background-color: #FF8A80">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span> (Inasistencias)
                            <br>
                            <span class="label" style="background-color: #80D8FF">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span> (Enfermedad general, Riesgo de trabajo)
                            <br>
                            <span class="label" style="background-color: #B2FF59">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span> (Dia otorgado)
                            <br>
                            <span class="label" style="background-color: #FFD180">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span> (Capacitacion/Trabajo fuera de planta)
                            <br>
                            <span class="label" style="background-color: #EA80FC">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span> (Maternidad/Paternidad)
                        </div>
                    </div>
                    <br>
                </div>
                <div class="box-footer">
                    <div class="col-lg-4 col-lg-offset-3">
                        <p style="color:red">Nota: Este proceso podría demorar algunos minutos</p>
                    </div>
                    <div class="col-lg-2">
                        <button class="btn btn-warning" id="generar" name="generar" type="submit">Generar</button>
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
            $('.chosen-select').prop('disabled', false).trigger("chosen:updated");
        }
        function chosDisable(){
            $('.chosen-select').prop('disabled', true).trigger("chosen:updated");
        }
    </script>

    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    
    <script>
            function GlobalData () {
                this.aData = <?php echo json_encode(\App\SUtils\SGuiUtils::getAreasAndDepts()) ?>;
                this.startOfWeek = <?php echo json_encode($startOfWeek) ?>;
                this.lEmployees = <?php echo json_encode($lEmployees) ?>;
                this.lAreas = this.aData[0];
                this.lDepts = this.aData[1];
            }
            
            var oData = new GlobalData();
        </script>
    <script src="{{ asset("assets/pages/scripts/filters/SFilter.js") }}" type="text/javascript"></script>

    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    
    <script src="{{ asset("assets/pages/scripts/report/SDelayReportGen.js") }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(function() {
        moment.locale('es');

        let dateStart = <?php echo json_encode(null) ?>;
        let dateEnd = <?php echo json_encode(null) ?>;
        let start = dateStart == null ? moment().startOf('week') : moment(dateStart);
        let end = dateEnd == null ? moment().endOf('week') : moment(dateEnd);

        var weekCuts = {};
        var biweekCuts = {};
        var biweekCalCuts = {};

        setRanges(start, start, end);

        cb(start, end);

        function cb(start, end) {
            $('#daterange-b-week span').html(start.format('D MMM YYYY') + ' - ' + end.format('D MMM YYYY'));

            let start_d = document.getElementById("start-date");
            let end_d = document.getElementById("end-date");
            let year_d = document.getElementById("year_id");
                
            start_d.setAttribute('value', start.format('YYYY-MM-DD'));
            end_d.setAttribute('value', end.format('YYYY-MM-DD'));
            year_d.setAttribute('value', start.format('YYYY'));
        }

        $('input[type=number][name=year]').on('change', function() {
            let year = document.getElementById("year_id").value;
            let start = moment(year + '-01-01');
            let end = moment(year + '-01-01');
            let pType = $('input[name=options]:checked').val();

            switch (pType) {
                case 'week':
                    end.add(6, 'days');
                break;
                case 'biweek':
                    end.add(13, 'days');
                break;
                case 'biweekcal':
                    end.add(14, 'days');
                break;
            }

            setRanges(start, start, end);
            cb(start, end);
        });

        function setRanges(dtDate, start, end) {
            let route = "{{ route('getcuts') }}";
            let weeks = [];
            let biweeks = [];
            let biweeksCal = [];

            let pType = $('input[name=options]:checked').val();
   
            axios.get(route, {
                params: {
                    "year" : dtDate.get('year')
                }
            })
            .then(res => {
                weeks = res.data.weeks.reverse();
                biweeks = res.data.biweeks.reverse();
                biweeksCal = res.data.biweekscal.reverse();

                weekCuts = {};
                biweekCuts = {};
                biweekCalCuts = {};

                for (const w of weeks) {
                    weekCuts["[Sem. " + w.number + "] - " + moment(w.dt_start).format('DD/MM/YYYY') + "-" + moment(w.dt_end).format('DD/MM/YYYY')] = 
                        [moment(w.dt_start), moment(w.dt_end)];
                }

                for (const b of biweeks) {
                    biweekCuts["[Qna. " + b.number + "] - " + moment(b.dt_start).format('DD/MM/YYYY') + "-" + moment(b.dt_end).format('DD/MM/YYYY')] = 
                        [moment(b.dt_start), moment(b.dt_end)];
                }

                for (const b of biweeksCal) {
                    biweekCalCuts["[Qna. " + b.number + "] - " + moment(b.dt_start).format('DD/MM/YYYY') + "-" + moment(b.dt_end).format('DD/MM/YYYY')] = 
                        [moment(b.dt_start), moment(b.dt_end)];
                }

                $('#daterange-b-week').daterangepicker({
                    autoApply: true,
                    startDate: start,
                    endDate: end,
                    alwaysShowCalendars: false,
                    maxDate: moment().add(1, 'days'),
                    ranges: pType == "week" ? weekCuts : pType == "biweek" ?  biweekCuts : biweekCalCuts,
                    drops: "auto"
                }, cb);
            })
            .catch(function(error) {
                console.log(error);
            });
        }

        $('#daterange-b-week').daterangepicker({
                    alwaysShowCalendars: false,
                    autoApply: true,
                    maxDate: moment().add(1, 'days'),
                    startDate: start,
                    endDate: end,
                    ranges: $(this).val() == "week" ? weekCuts : $(this).val() == "biweek" ? biweekCuts : biweekCalCuts,
                    drops: "auto"
                }, cb);

        $('input[type=radio][name=options]').on('change', function() {
            let start = moment(end);
            switch ($(this).val()) {
                case 'week':
                        start.subtract(6, 'days');
                        $('#daterange-b-week').daterangepicker({
                            alwaysShowCalendars: false,
                            autoApply: true,
                            maxDate: moment().add(1, 'days'),
                            startDate: start,
                            endDate: end,
                            ranges: weekCuts,
                            drops: "auto"
                        }, cb);
                break;
                case 'biweek':
                        start.subtract(13, 'days');
                        $('#daterange-b-week').daterangepicker({
                            alwaysShowCalendars: false,
                            autoApply: true,
                            maxDate: moment().add(1, 'days'),
                            startDate: start,
                            endDate: end,
                            ranges: biweekCuts,
                            drops: "auto"
                        }, cb);
                break;
                case 'biweekcal':
                        start.subtract(14, 'days');
                        $('#daterange-b-week').daterangepicker({
                            alwaysShowCalendars: false,
                            autoApply: true,
                            maxDate: moment().add(1, 'days'),
                            startDate: start,
                            endDate: end,
                            ranges: biweekCalCuts,
                            drops: "auto"
                        }, cb);
                break;
            }

            cb(start, end);
        });
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