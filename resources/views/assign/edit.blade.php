@extends("theme.$theme.layout")
@section('title')
    Asignacion horarios
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/schedule/copiar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/schedule/bloquear.js")}}" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
<script src="{{asset("assets/pages/scripts/fecha.js")}}" type="text/javascript"></script>
<script>
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
        });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Modificar asignacion horario fijo</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('asignacion')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('actualizar_asignacion', ['id' => $datas->id])}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf @method("put")
                <div class="box-body">
                    @include('assign.form')
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