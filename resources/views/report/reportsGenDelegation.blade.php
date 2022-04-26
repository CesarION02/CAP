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
                            <div class="col-md-4 col-md-offset-1">
                                <select name="pay_way" id="pay_way" class="form-control" v-model="iPayWay">
                                    <option value="2" selected>Semana</option>
                                    <option value="1">Quincena</option>
                                </select>
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
                    <div class="row">
                        <div class="col-md-2">
                            Prenóminas delegadas:
                        </div>
                        <div class="col-md-4 col-md-offset-1">
                            <div class="form-group">
                              <label for=""></label>
                              <select v-if="iPayWay == 2" v-model="coDates" class="form-control" v-on:change="onCutoffDateChange()" placeholder="Seleccione prenómina" required>
                                <option v-for="sCut in oPayrolls.weeks" 
                                        :value="sCut.start_date + '_' + sCut.end_date + '_' + sCut.number + '_' + sCut.year" 
                                        v-on:change="onCutoffDateChange()">
                                   [Sem. @{{ sCut.number }}] / @{{ sCut.start_date }} - @{{ sCut.end_date }}
                                </option>
                              </select>
                              <select v-else v-model="coDates" class="form-control" v-on:change="onCutoffDateChange()" placeholder="Seleccione prenómina" required>
                                <option v-for="qCut in oPayrolls.biweeks" 
                                        :value="qCut.start_date + '_' + qCut.end_date + '_' + qCut.number + '_' + qCut.year" 
                                        v-on:change="onCutoffDateChange()">
                                   [Qna. @{{ qCut.number }}] / @{{ qCut.start_date }} - @{{ qCut.end_date }}
                                </option>
                              </select>
                            </div>
                        </div>
                        <input v-model="startDate" type="hidden" name="start_date">
                        <input v-model="endDate" type="hidden" name="end_date">
                        <input v-model="payrollNumber" type="hidden" name="payroll_number">
                        <input v-model="payrollYear" type="hidden" name="year">
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
