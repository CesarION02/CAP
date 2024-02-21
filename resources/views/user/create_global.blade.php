@extends("theme.$theme.layout")
@section('title')
    Usuarios
@endsection

@section("scripts")
    <script src="{{ asset('assets/pages/scripts/user/userGlobal.js') }}"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Crear usuario</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('usuario')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('guardar_usuario_global')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    @include('user.form_global')
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button type="submit" class="btn btn-success" id="guardar" disabled>Guardar</button>
                        <button type="reset" onclick="resetTheSelect()" id="deshacer" class="btn btn-default" disabled>Deshacer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection