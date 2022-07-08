@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
@endsection
@section('title')
    Checada
@endsection

@section("scripts")
<script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
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
                <h3 class="box-title">Modificar checadas manuales</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('checada')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('actualizar_checada', ['id' => $datas->id])}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf @method("put")
                <div class="box-body">
                    @include('register.form')
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button type="submit" class="btn btn-success" id="guardar">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection