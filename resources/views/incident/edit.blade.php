@extends("theme.$theme.layout")
@section('title')
    Incidencias
@endsection

@section('content')
<div class="row" id="incidentsApp">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Modificar incidencia</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('incidentes')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('actualizar_incidente', ['id' => $datas->id])}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf @method("put")
                <div class="box-body">
                    @include('incident.form')
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        @include('includes.button-form-edit')
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/fecha.js")}}" type="text/javascript"></script>
    {{-- <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script> --}}
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    {{-- <script src="{{ asset("assets/pages/scripts/specialw/specialw.js")}}" type="text/javascript"></script> --}}
    {{-- <script src="{{ asset("assets/pages/scripts/incidentsEmployeesView/tipoIncidencia.js")}}" type="text/javascript"></script> --}}
    
    <script>
        function GlobalData () {
            this.incidentTypeId = <?php echo json_encode($incidentTypeId) ?>;
            this.lCommControl = <?php echo json_encode($lCommControl) ?>;
            this.isEditing = true;
        }

        var oGlobalData = new GlobalData();
    </script>
    <script src="{{ asset("assets/pages/scripts/incidents/SVueIncidents.js")}}" type="text/javascript"></script>
@endsection