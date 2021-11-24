@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
@endsection
@section('title')
Crear Configuración
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Crear Configuración</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route('save_cfg') }}" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    @include('prepayrollcontrol.voboscfgform')
                </div>
                <div class="box-footer">
                    <div class="col-md-3 col-md-offset-9">
                        <button class="btn btn-primary" id="generar" name="generar" type="submit">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    
@endsection