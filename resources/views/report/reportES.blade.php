@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
@endsection
@section('title')
Reporte Entradas/Salidas
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte ES</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route('generarreporteES') }}">
                <input type="hidden" id="reportType" name="reportType" value={{ $reportType }}>
                <div class="box-body" id="reportApp">
                    <div class="row">
                        <div class="col-md-5 col-md-offset-1">
                            <label for="start_date">Fecha inicio:</label>
                            <input type="date" name="start_date" id="start_date" :value="startDate">
                        </div>
                        <div class="col-md-5">
                            <label for="end_date">Fecha fin:</label>
                            <input type="date" name="end_date" id="end_date" :value="endDate">
                        </div>
                    </div>
                    <br>
                    <div class="row" v-if="oData.reportType == 1">
                        <div class="col-md-10 col-md-offset-1">
                            <label for="cars">Elige área(s):</label>
                            
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="cars" name="vals[]" multiple>
                                <option v-for="area in oData.areas" :value="area.id">@{{ area.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" v-else-if="oData.reportType == 2">
                        <div class="col-md-10 col-md-offset-1">
                            <label for="cars">Elige grupo(s):</label>
                            
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="cars" name="vals[]" multiple>
                                <option v-for="group in oData.deptsGroups" :value="group.id">@{{ group.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" v-else-if="oData.reportType == 3">
                        <div class="col-md-10 col-md-offset-1">
                            <label for="cars">Elige departamento(s):</label>
                            
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="cars" name="vals[]" multiple>
                                <option v-for="dept in oData.departaments" :value="dept.id">@{{ dept.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" v-else-if="oData.reportType == 4">
                        <div class="col-md-10 col-md-offset-1">
                            <label for="cars">Elige empleado(s):</label>
                            
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="cars" name="vals[]" multiple>
                                <option v-for="employee in oData.employees" :value="employee.id">@{{ employee.name + ' ' + employee.num_employee }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" v-else-if="oData.reportType == 5">
                        <div class="col-md-10 col-md-offset-1">
                            <label for="cars">Elige empleado(s):</label>
                            
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="cars" name="vals[]" multiple>
                                <option v-for="employee in oData.employees" :value="employee.id">@{{ employee.name + ' ' + employee.num_employee }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button class="btn btn-warning" id="generar" name="generar" type="submit">Generar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src="{{ asset("assets/pages/scripts/report/generar.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    
    <script>
        function GlobalData () {
            this.reportType = <?php echo json_encode($reportType) ?>;
            this.areas = <?php echo json_encode($lAreas) ?>;
            this.deptsGroups = <?php echo json_encode($lDepsGroups) ?>;
            this.departaments = <?php echo json_encode($lDepts) ?>;
            this.employees = <?php echo json_encode($lEmployees) ?>;
        }
        
        var oData = new GlobalData();
    </script>

    <script src="{{asset("assets/js/vues/report_regs.js")}}" type="text/javascript"></script>

    <script>
        $(".chosen-select").chosen();
    </script>
@endsection