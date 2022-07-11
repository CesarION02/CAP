@extends("theme.$theme.layout")
@section('title')
{{ ($becario ? 'Practicantes' : 'Empleados') }}
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/employee/puesto.js")}}" type="text/javascript"></script>
    <script>
        var rutaPuesto = '<?php echo $rutaPuesto; ?>';
    </script>
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
                <h3 class="box-title">Modificar {{ ($becario ? 'practicante' : 'empleado') }}</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('empleado')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('actualizar_empleado', ['id' => $data->id])}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf @method("put")
                <div class="box-body">
                    @include('employee.form')
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