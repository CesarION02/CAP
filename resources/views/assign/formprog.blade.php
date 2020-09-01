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
        <select style="width: 95%" class="js-example-basic-multiple" name="empleado" id="empleado">
            @foreach($employees as $employee)
                <option value="{{$employee->id}}">{{$employee->name}}</option>
            @endforeach
        </select>    
    </div>
</div>
<div class="form-group">
    <label for="horario" class="col-lg-3 control-label requerido">Plantilla:</label>
    <div class="col-lg-4">
        <select name="horario1" id="horario1">
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
    <label for="orden" class="col-lg-2 control-label requerido">Orden:</label>
    <div class="col-md-1">
        <input type="number" name="orden1" id="orden1" value="1" style="width:70%" disabled>
    </div>
    <div class="col-lg-1">
        <button type="button" class="btn btn-primary" onclick="agregar()"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button>
    </div>
    <div class="col-lg-1">
        <button type="button" class="btn btn-primary" onclick="eliminar()"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>
    </div>
    <input type="hidden" name="contador" id="contador" value="1">
    <input type="hidden" name="nameGroup" id="nameGroup" value="{{$employees[0]->nameGroup}}">
    <input type="hidden" name="idGroup" id="idGroup" value="{{$idGroup}}">
</div>