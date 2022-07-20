@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
@endsection
@section('title')
empleados por grupo departamento
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">empleados por grupo departamento</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:emmpleadosgrupodepartamento"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <form id="form" action="{{route('empl_group_assign_generate')}}">
                <div class="box-body" id="reportApp">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-1">
                            <label for="tipo">Generar reporte por:</label>
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select tipo" id="tipo" name="tipo" required> 
                                <option value="">Selecciona opciones..</option>
                                <option value="1">Por grupo departamento</option>
                                <option value="2">Por departamento</option>
                                <option value="3">Por supervisor</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-1">
                            <label for="deptGrp">Elige grupo departamento:</label>
                            <select disabled="true" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="deptGrp" name="deptGrp" required>
                                <option value="0">Todos</option>
                                @foreach ($grupos as $gr)
                                    <option value="{{$gr->id}}">{{$gr->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-1">
                            <label for="dept">Elige departamento:</label>
                            <select disabled="true" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="dept" name="dept" required>
                                <option value="0">Todos</option>
                                @foreach ($departments as $dept)
                                    <option value="{{$dept->id}}">{{$dept->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-1" id='selectemp'>
                            <label for="supervisor">Elige supervisor:</label>
                            <select disabled="true" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="supervisor" name="supervisor" required>
                                    <option value="0">Todos</option>
                                    @foreach ($supervisores as $sup)
                                        <option value="{{$sup->id}}">{{$sup->name}}</option>
                                    @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="" style="float: right;">
                        <button id="generar" class="btn btn-warning" type="submit">Generar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).on('change', '.tipo', function(e) {
            var tipo = $(e.currentTarget).val();
            if (tipo == 0) {
                $('#deptGrp').prop('disabled', true).trigger("chosen:updated");
                $('#dept').prop('disabled', true).trigger("chosen:updated");
                $('#supervisor').prop('disabled', true).trigger("chosen:updated");
            } else if (tipo == 1) {
                $('#deptGrp').prop('disabled', false).trigger("chosen:updated");
                $('#dept').prop('disabled', true).trigger("chosen:updated");
                $('#supervisor').prop('disabled', true).trigger("chosen:updated");

            } else if (tipo == 2) {
                $('#deptGrp').prop('disabled', true).trigger("chosen:updated");
                $('#dept').prop('disabled', false).trigger("chosen:updated");
                $('#supervisor').prop('disabled', true).trigger("chosen:updated");
            } else if (tipo == 3) {
                $('#deptGrp').prop('disabled', true).trigger("chosen:updated");
                $('#dept').prop('disabled', true).trigger("chosen:updated");
                $('#supervisor').prop('disabled', false).trigger("chosen:updated");
            }
        });
    </script>
    <script>
        $(".chosen-select").chosen();
    </script>
@endsection