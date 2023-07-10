@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
@endsection
@section('title')
    Checadas
@endsection

@section('content')
<div class="row" id="divRegistries">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Crear checada</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:agregarchecadas"])
                <div class="box-tools pull-right">
                    <a href="{{route('checada')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('guardar_checada')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    @include('register.form')
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button type="button" class="btn btn-success" id="guardar" v-on:click="store();">Guardar</button>
                        <button type="button" v-on:click="resetCreate();" class="btn btn-default">Deshacer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section("scripts")
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vues/SVueRegistries.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script>
        var oGui = new SGui();
        $(".chosen-select").chosen();
    </script>
    <script>
        $(document).ready( function (){
            $('#selEmployee').on('change', function(event, params) {
                app.employee = $('#selEmployee').val();
                app.date = null;
                app.time = null;
                app.type = 0;
                app.canCheck = false;
            });

            $('#date').on('change', function(event, params) {
                app.time = null;
                app.type = 0;
                app.canCheck = false;
            });
        })
    </script>
@endsection