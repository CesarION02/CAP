@extends("theme.$theme.layout")
@section('title')
Usuarios vs. dispositivos biométricos
@endsection
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
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
                <h3 class="box-title">Usuarios vs. dispositivos biométricos</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('usuariosvsdispositivos')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('guardar_usuariosvsdispositivos')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    @include('uservsdevices.form')
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