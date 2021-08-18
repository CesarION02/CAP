<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Empleado:</label>
    <div class="col-lg-8">
        
            @if(isset($datas))
                <select name="employee_id" id="employee_id">
                    <option value="0">Seleccione empleado</option>
                    @for($i = 0 ; count($employees) > $i ; $i++)
                        @if($datas->employee_id == $employees[$i]->id)
                            <option selected value="{{$employees[$i]->id}}">{{$employees[$i]->nameEmployee}}</option>
                        @else
                            <option value="{{$employees[$i]->id}}">{{$employees[$i]->nameEmployee}}</option>
                        @endif
                    @endfor
            @else
                <select name="employee_id" id="employee_id">
                    <option value="0">Seleccione empleado</option>
                    @for($i = 0 ; count($employees) > $i ; $i++)
                        <option value="{{$employees[$i]->id}}">{{$employees[$i]->nameEmployee}}</option>
                    @endfor
            @endif
        </select>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Fecha:</label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            <input type="date" name="date" id="date" value="{{$datas->date}}">
        @else
            <input type="date" name="date" id="date">
        @endif
        
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Hora:</label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            <input type="time" name="time" id="time" value="{{$datas->time}}">
        @else
            <input type="time" name="time" id="time">
        @endif
        
    </div>
</div>
@if(! isset($datas))
    <div class="form-group">
        <label for="nombre" class="col-lg-3 control-label requerido" for="optradio">Tipo checada:</label>
        <div class="row">
            <div class="col-md-2">
                <label><input v-model="picked" v-on:change="onTypeChange()" type="radio" name="optradio" value="single">Sencilla</label>
            </div>
            <div class="col-md-2">
                <label><input v-model="picked" v-on:change="onTypeChange()" type="radio" name="optradio" value="cut">Corte</label>
            </div>
        </div>
    </div>
@endif
<div class="form-group" v-if="isSingle">
    <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Tipo checada:</label>
    <div class="col-lg-8">

        @if(isset($datas))
            @if($datas->type_id == 1)
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
        @else
            <select name="type_id" id="type_id">
                <option value="0">Seleccione tipo</option>
                <option value="1">Entrada</option>
                <option value="2">Salida</option>
            </select>
        @endif
        
    </div>
</div>
