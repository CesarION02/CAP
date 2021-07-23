<div class="form-group">
    <label for="start_date" class="col-lg-3 control-label requerido">Fecha:</label>
    <div class="col-lg-3">
        @if(isset($datas))
            <input type="date" name="start_date" id="start_date" value="{{$datas->start_date}}" >
        @else
        <input type="date" name="start_date" id="start_date">
        @endif
    </div>   
</div> 

<div class="form-group">
    <label for="empleado" class="col-lg-3 control-label requerido">Colaboradores:</label>
    <div class="col-lg-8">
        <select style="width: 95%" class="js-example-basic-multiple" name="empleado" id="empleado">
            @foreach($employees as $employee)
                <option value="{{$employee->id}}">{{$employee->name}}</option>
            @endforeach
        </select>    
    </div>
</div>
<div class="form-group">
    <label for="horario" class="col-lg-3 control-label requerido">Horario:</label>
    <div class="col-lg-4">
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