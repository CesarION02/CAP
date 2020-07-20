<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Empleado:</label>
    <div class="col-lg-8">
        
            @if(isset($datas))
                <input type="text" name="employee_id" id="employee_id" value="{{$datas[0]->nameEmp}}" readonly>
            @else
                <select name="employee_id" id="employee_id">
                    <option value="0">Seleccione Empleado</option>
                    @for($i = 0 ; count($employees) > $i ; $i++)
                        <option value="{{$employees[$i]->idEmployee}}">{{$employees[$i]->nameEmployee}}</option>
                    @endfor
            @endif
        </select>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Turno:</label>
    <div class="col-lg-8">
        <select name="workshift_id" id="workshift_id">
            @if(isset($datas))
                <option value="0">Seleccione Turno</option>
                @for($i = 0 ; count($workshifts) > $i ; $i++)
                    @if($datas[0]->idWork == $workshifts[$i]->id)
                        <option selected value="{{$workshifts[$i]->id}}">{{$workshifts[$i]->name.' '.$workshifts[$i]->entrada.' - '.$workshifts[$i]->salida}}</option>
                    @else
                        <option value="{{$workshifts[$i]->id}}">{{$workshifts[$i]->name.' '.$workshifts[$i]->entrada.' - '.$workshifts[$i]->salida}}</option>
                    @endif
                @endfor 
            @else
                <option value="0">Seleccione Turno</option>
                @for($i = 0 ; count($workshifts) > $i ; $i++)
                    <option value="{{$workshifts[$i]->id}}">{{$workshifts[$i]->name.' '.$workshifts[$i]->entrada.' - '.$workshifts[$i]->salida}}</option>
                @endfor
            @endif
        </select>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Fecha:</label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            <input type="date" name="date" id="date" value="{{$datas[0]->date}}">
        @else
            <input type="date" name="date" id="date">
        @endif
        
    </div>
</div>
