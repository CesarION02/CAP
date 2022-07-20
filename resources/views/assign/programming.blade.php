@extends("theme.$theme.layout")
@section('title')
Asignar horario fijo
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/assign/bloquear.js")}}" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
<script src="{{asset("assets/pages/scripts/assign/agregar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/assign/eliminar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/fechasHorario.js")}}" type="text/javascript"></script>
<script>
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
            // if(withEmp){
            //     document.getElementById('empleado').setAttribute('disabled', 'disabled');
            // }
        });
</script>
<script>
    var withEmp = <?php echo json_encode($withEmp); ?>;
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Asignar horario fijo</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:asignacionpordept"])
                <div class="box-tools pull-right">
                    <a href="{{route('index_programacion')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('guardar')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    @include('assign.formprog')
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button type="submit" class="btn btn-success" id="guardar">Guardar</button>
                        <button type="button" onclick="" class="btn btn-default">Deshacer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection