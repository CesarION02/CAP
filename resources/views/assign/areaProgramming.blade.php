@extends("theme.$theme.layout")
@section('title')
Asignar horario fijo
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/assign/bloquear.js")}}" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
<script src="{{asset("assets/pages/scripts/assign/agregar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/assign/eliminar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/fechasHorario.js")}}" type="text/javascript"></script>
<script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    });
</script>
<script>
    function GlobalData () {
        this.getAreaProgRoute = <?php echo json_encode($getAreaProgRoute); ?>;
        this.storeRoute = <?php echo json_encode($storeRoute); ?>;
        this.indexRoute = <?php echo json_encode($indexRoute); ?>;
        this.datas = <?php echo isset($datas) ? json_encode($datas) : json_encode(null); ?>;
    }
    var oData = new GlobalData();
    var oGui = new SGui();
</script>
<script src="{{ asset("assets/pages/scripts/assign/vueAreaProgramming.js") }}" type="text/javascript"></script>
@endsection

@section('content')
<div class="row" id="areaPrograming">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Asignar horario fijo</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:asignacionpordept"])
                <div class="box-tools pull-right">
                    <a href="{{route('index_areaProgramming')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form id="form-general" class="form-horizontal" autocomplete="off">
                <div class="box-body">
                    @include('assign.formAreaProgramming')
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button type="button" class="btn btn-success" id="guardar" v-on:click="getAreaProgamming();">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection