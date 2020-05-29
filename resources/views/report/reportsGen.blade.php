@extends("theme.$theme.layout")

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
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
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route(''.$sRoute.'') }}">
                <div class="box-body" id="reportApp">
                    <div class="row">
                        <div class="col-md-2">
                            Periodicidad de pago*:
                        </div>
                        <div class="col-md-4 col-md-offset-1">
                            <select name="pay_way" id="pay_way" class="form-control">
                                <option value="2">Semana</option>
                                <option value="1">Quincena</option>
                                <option value="0">Todos</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-2">
                            Filtrar*:
                        </div>
                        <div class="col-md-7 col-md-offset-1">
                            @include('filters.adept')
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-2">
                            Período*:
                        </div>
                        <div class="col-md-6 col-md-offset-1">
                            <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                        <input :value="startDate" type="hidden" name="start_date">
                        <input :value="endDate" type="hidden" name="end_date">
                    </div>
                </div>
                <div class="box-footer">
                    <div class="col-lg-8"></div>
                    <div class="col-lg-2">
                        <button class="btn btn-warning" id="generar" name="generar" type="submit">Generar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    
    <script>
            function GlobalData () {
                this.aData = <?php echo json_encode(\App\SUtils\SGuiUtils::getAreasAndDepts()) ?>;
                this.startOfWeek = <?php echo json_encode($startOfWeek) ?>;
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
                startDate: start,
                endDate: end,
                ranges: {
                   'Hoy': [moment(), moment()],
                   'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'Esta semana': [startWeek, endWeek],
                   'Semana pasada': [startLastWeek, endLastWeek],
                   'Esta quincena': [startThisFortnight, endThisFortnight],
                   'Quincena pasada': [startLastFortnight, endLastFortnight],
                   'Este mes': [moment().startOf('month'), moment().endOf('month')],
                   'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);
        
            cb(start, end);
        
        });

        $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
            app.setDates(picker.startDate.format('YYYY-MM-DD'), picker.endDate.format('YYYY-MM-DD'));
        });
    </script>
@endsection
