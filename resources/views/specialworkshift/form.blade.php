<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Empleado:</label>
    <div class="col-lg-8">
        
            @if(isset($datas))
                <select name="employee_id" id="employee_id">
                    <option value="0">Seleccione empleado</option>
                    @for($i = 0 ; count($employees) > $i ; $i++)
                        @if($employees[$i]->idEmployee == $datas[0]->employee_id)
                            <option selected value="{{$employees[$i]->idEmployee}}">{{$employees[$i]->nameEmployee}}</option>
                        @else
                            <option value="{{$employees[$i]->idEmployee}}">{{$employees[$i]->nameEmployee}}</option>
                        @endif
                    @endfor
            @else
                <select name="employee_id" id="employee_id" class="chosen-select">
                    <option value="0">Seleccione empleado</option>
                    @for($i = 0 ; count($employees) > $i ; $i++)
                        <option value="{{$employees[$i]->idEmployee}}">{{$employees[$i]->nameEmployee.' - '.$employees[$i]->numEmployee}}</option>
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
                <option value="0">Seleccione turno</option>
                @for($i = 0 ; count($workshifts) > $i ; $i++)
                    @if($datas[0]->workshift_id == $workshifts[$i]->id)
                        <option selected value="{{$workshifts[$i]->id}}">{{$workshifts[$i]->name.' '.$workshifts[$i]->entrada.' - '.$workshifts[$i]->salida}}</option>
                    @else
                        <option value="{{$workshifts[$i]->id}}">{{$workshifts[$i]->name.' '.$workshifts[$i]->entrada.' - '.$workshifts[$i]->salida}}</option>
                    @endif
                @endfor 
            @else
                <option value="0">Seleccione turno</option>
                @for($i = 0 ; count($workshifts) > $i ; $i++)
                    <option value="{{$workshifts[$i]->id}}">{{$workshifts[$i]->name.' '.$workshifts[$i]->entrada.' - '.$workshifts[$i]->salida}}</option>
                @endfor
            @endif
        </select>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Fecha inicio:</label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            <input type="date" name="datei" id="datei" value="{{$datas[0]->dateI}}">
        @else
            <input type="date" name="datei" id="datei">
        @endif
        
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Fecha fin:</label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            <input type="date" name="dates" id="dates" value="{{$datas[0]->dateS}}">
        @else
            <input type="date" name="dates" id="dates">
        @endif
        
    </div>
</div>
