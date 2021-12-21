@extends("theme.$theme.layout")
@section('title')
Asignar horario
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/assign/bloquear.js")}}" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
<script src="{{asset("assets/pages/scripts/fechaHorario.js")}}" type="text/javascript"></script>
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
                <h3 class="box-title">Asignar horario</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('sinprogramacion')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('guardar_without')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="horario" class="col-lg-3 control-label requerido">Horario:</label>
                        <div class="col-lg-3">
                            <select name="horario" id="horario">
                                @foreach($schedule_template as $schedule_template => $index)
                                    @if(isset($datas))
                                        @if($datas->schedule_template_id == $index)
                                            <option selected value="{{$index}}">{{$schedule_template}}</option>
                                        @else
                                            <option value="{{$index}}">{{$schedule_template}}</option>
                                        @endif
                                    @else
                                        <option value="{{$index}}">{{$schedule_template}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="start_date" class="col-lg-3 control-label requerido">Fecha inicial:</label>
                        <div class="col-lg-3">
                            @if(isset($datas))
                                <input type="date" name="start_date" id="start_date" value="{{$datas->start_date}}" >
                            @else
                            <input type="date" name="start_date" id="start_date">
                            @endif
                        </div>  
                        <label for="departamento" class="col-lg-2 control-label">Fecha final:</label>
                        <div class="col-lg-3">
                            @if(isset($datas))
                                <input type="date" name="end_date" id="end_date" value="{{$datas->end_date}}" >
                            @else
                                <input type="date" name="end_date" id="end_date">
                            @endif
                        </div> 
                    </div>   
                    <div class="form-group">
                        <label for="empleado" class="col-lg-3 control-label requerido">Empleados:</label>
                        <div class="col-lg-8">
                            <input type="text" name="employee" id="employee" value="{{$employee->name}}">
                            <input type="hidden" name="idemp" id="idemp" value="{{$employee->idemp}}">
                        </div>
                    </div>
                
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