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
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route(''.$sRoute.'') }}" id="theForm">
                <div class="box-body" id="reportApp">
                    <div class="row">
                        <div class="col-md-3 requerido">
                            Filtrar por:*
                        </div>
                        <div class="col-md-3 col-md-offset-1">
                            <label><input v-model="picked" v-on:change="onFilterTypeChange()" type="radio" name="optradio" value="period" onclick="chosDisable();">Periodicidad de pago</label>
                        </div>
                        <div class="col-md-3">
                            <label><input v-model="picked" v-on:change="onFilterTypeChange()" type="radio" name="optradio" value="employee" onclick="chosEnable();">Empleado</label>
                        </div>
                    </div>
                    <br>
                    <div>
                        <div class="row">
                            <div class="col-md-3">
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
                        <div class="row">
                            <div class="col-md-3">
                                Empleado:*
                            </div>
                            <div class="col-md-7 col-md-offset-1">
                                <select :disabled="picked == 'period'" v-model='idEmployee' name="emp_id" form="theForm" id="emp_id" class="form-control chosen-select" data-placeholder="Selecciona empleado...">
                                    <option v-for="employee in lEmps" :value="employee.id">@{{ employee.name }} - @{{employee.num_employee}}</option>
                                </select>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            Filtrar:*
                        </div>
                        <div class="col-md-7 col-md-offset-1">
                            @include('filters.adept')
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-3 requerido">
                            Quitar empleados que no checan:
                        </div>
                        <div class="col-md-3 col-md-offset-1">
                            <input id="cbx1" type="checkbox" name="nochecan" value="nochecan" checked>
                        </div>
                    </div>
                    <br>
                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            Rango de fechas:*
                        </div>
                        <div class="col-md-7 col-md-offset-1">
                            {{-- <input type="date" name="start_date" class="form-control" required>
                            <input type="date" name="end_date" class="form-control" required> --}}
                            @include('controls.b-week', ['start_date_v' => null, 'end_date_v' => null,
                                                        'start_date_name' => 'start_date', 'end_date_name' => 'end_date']) 
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="col-lg-4 col-lg-offset-4">
                        <p style="color:red">Nota: Este proceso podría demorar algunos minutos</p>
                    </div>
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
    <script>
        function chosEnable(){
            $('.chosen-select').prop('disabled', false).trigger("chosen:updated");
        }
        function chosDisable(){
            $('.chosen-select').prop('disabled', true).trigger("chosen:updated");
        }
    </script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
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
    <script>
        $(".chosen-select").chosen();
    </script>
@endsection
