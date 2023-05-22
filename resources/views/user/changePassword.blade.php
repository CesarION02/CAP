@extends("theme.$theme.layout")
@section('title')
    Cambiar Contraseña
@endsection

@section("scripts")

@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Cambiar contraseña</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:cambiocontrasena"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{route('actualizar_contraseña', ['id' => $data->id])}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf @method("put")
                <div class="box-body">
                    @include('user.formChange')
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