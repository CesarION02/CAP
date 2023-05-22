@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
@endsection
@section('title')
Modificar Configuración
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Modificar Configuración</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:configvobo"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route('update_cfg') }}" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                @method("put")
                <div class="box-body">
                    <input type="hidden" name="id_configuration" value="{{ $oCfg->id_configuration }}">
                    @include('prepayrollcontrol.voboscfgform')
                </div>
                <div class="box-footer">
                    <div class="col-md-3 col-md-offset-9">
                        <button class="btn btn-info" type="submit">Modificar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    
@endsection