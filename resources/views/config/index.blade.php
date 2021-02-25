@extends("theme.$theme.layout")
@section('title')
    Configuraciones
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
                <h3 class="box-title">Configuraciones</h3>
                <div class="box-tools pull-right">
                   
                </div>
            </div>
            <form  id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    @include('config.form')
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection