@extends("theme.$theme.layout")
@section('title')
Fecha Específica
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/assign/bloquear.js")}}" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
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
                <h3 class="box-title">Selccionar fecha exacta</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('asignacion')}}" class="btn btn-block btn-info btn-sm">
                        
                    </a>
                </div>
            </div>
            <form action="{{route('mostrar_fecha')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    @include('assign.formSpecif')
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button type="submit" class="btn btn-success">Consultar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection