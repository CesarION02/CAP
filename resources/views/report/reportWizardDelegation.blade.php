@extends("theme.$theme.layout")

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
    <link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
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
            <form action="{{ route(''.$sRoute.'') }}" id="theForm">
                <div class="box-body" id="reportApp">
                    <input type="hidden" name="delegation" value="1">
                    <input type="hidden" name="optradio" value="period">
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
                    <div class="row">
                        <div class="col-md-2 requerido">
                            Quitar empleados que no checan:
                        </div>
                        <div class="col-md-3 col-md-offset-1">
                            <input id="cbx1" type="checkbox" name="nochecan" value="nochecan" checked>
                        </div>
                    </div>
                    <br>
                    @include('report.delegation.delegations')
                    <input type="hidden" id="wizard" name="wizard" value="{{ $wizard }}">
                    <input type="hidden" id="delegation" name="delegation" value="{{ is_null($oPayrolls) ? 0 : 1 }}">
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
        $('#reportDelayAppGen :input').prop('disabled', true);
    </script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script>
        var oGui = new SGui();
        oGui.showLoadingBlocked(4000);
    </script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    
    <script>
        function GlobalData () {
            this.aData = <?php echo json_encode(\App\SUtils\SGuiUtils::getAreasAndDepts()) ?>;
            this.startOfWeek = <?php echo json_encode($startOfWeek) ?>;
            this.lEmployees = <?php echo json_encode(isset($lEmployees) ? $lEmployees : []) ?>;
            this.oPayrolls = <?php echo json_encode($oPayrolls) ?>;
        }
        
        var oData = new GlobalData();
    </script>
    <script src="{{ asset("assets/pages/scripts/filters/SFilter.js") }}" type="text/javascript"></script>

    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    
    <script src="{{ asset("assets/pages/scripts/report/SDelayReportGen.js") }}" type="text/javascript"></script>

    <script>
        $(document).ready(function() {
            $(window).on('load', function() {
                $('#reportDelayAppGen :input').prop('disabled', false);
            });
            $("#cbx1").click(function() {
                if ($(this).is(":checked")){
                  doChecked(); // Función si se checkea
                } else {
                  doNotChecked(); //Función si no
                }
            });
         });
    </script>
@endsection
