@extends("theme.$theme.layout")
@section('title')
Asignar horario fijo
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
            <form id="assignIdForm" action="{{route('guardar')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
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

@section("scripts")
<script src="{{asset("assets/pages/scripts/assign/bloquear.js")}}" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
<script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/assign/agregar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/assign/eliminar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/fechasHorario.js")}}" type="text/javascript"></script>
<script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/pages/scripts/SValidations.js") }}" type="text/javascript"></script>
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
    var routeValSch = <?php echo json_encode($route_validate_schedule); ?>;
    var idAssign = null;
</script>
<script>
    const form = document.querySelector('#assignIdForm');
    var oGui = new SGui();

    form.addEventListener('submit', function (e) {
        // prevent the form from submitting
        e.preventDefault();

        // get the values submitted in the form
        const startDate = document.querySelector('#start_date').value;
        const endDate = document.querySelector('#end_date').value;
        const idEmployee = document.querySelector('#empleado').value;

        // Validación de las fechas de inicio y fin y que el id del empleado sea mayor que 0
        if (startDate == "") {
            oGui.showError("Debe seleccionar una fecha de inicio");
            return;
        }
        if (idEmployee == 0) {
            oGui.showError("Debe seleccionar un empleado");
            return;
        }

        let oValidation = new SValidations();
        oValidation.validateSchedule(startDate, endDate, idEmployee, routeValSch, "assignIdForm", "assign", idAssign);
    });
</script>
@endsection