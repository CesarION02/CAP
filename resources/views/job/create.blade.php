@extends("theme.$theme.layout")
@section('title')
    Puestos
@endsection

@section("scripts")
<script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.select2-class').select2();
        })
    </script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Crear puesto</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('puesto')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('guardar_puesto')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    @include('job.form')
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