@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
@endsection
@section('title')
    Incidencias
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/fecha.js")}}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/specialw/specialw.js")}}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/incidentsEmployeesView/tipoIncidencia.js")}}" type="text/javascript"></script>
    <script>
        $(".chosen-select").chosen();
    </script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">@switch($incidentType)
                    @case(14)
                        Asignar otras incidencias
                        @break
                    @case(2)
                        
                        @break
                    @default
                        Crear incidencia
                @endswitch</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('incidentes', [isset($incidentType) ? $incidentType : 0])}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('guardar_incidente')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    @include('incident.form')
                    <input id="incident_type" name="incident_type" type="hidden" value="{{ $incidentType }}">
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        @include('includes.button-form-create')
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection