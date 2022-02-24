@extends("theme.$theme.layout")
@section('title')
    Checada
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
                <h3 class="box-title">Modificar checadas</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('registros', $id)}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('actualizar_registros', ['id' => $registros[0]->id])}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf @method("put")
                <input type="hidden" name="id" id="id" value="{{$id}}">
                <div class="box-body">
                    <div class="form-group">
                        <label for="nombre" class="col-lg-3 control-label requerido">Empleado:</label>
                        <div class="col-lg-8">
                            <input type="text" name="employee" value="{{$registros[0]->nombre}}" style="width : 300px;" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Fecha:</label>
                        <div class="col-lg-8">
                            <input type="date" name="date" id="date" value="{{$registros[0]->fecha}}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Hora:</label>
                        <div class="col-lg-8">
                            <input type="time" name="time" id="time" value="{{$registros[0]->hora}}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Tipo checada:</label>
                        <div class="col-lg-8">
                            @if($registros[0]->tipo == 1)
                                <select name="type_id" id="type_id">
                                    <option value="0">Seleccione tipo</option>
                                    <option selected value="1">Entrada</option>
                                    <option value="2">Salida</option>
                                </select>
                            @else
                                <select name="type_id" id="type_id">
                                    <option value="0">Seleccione tipo</option>
                                    <option value="1">Entrada</option>
                                    <option selected value="2">Salida</option>
                                </select>
                            @endif

                        </div>
                    </div>
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