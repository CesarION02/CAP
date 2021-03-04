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
    <input type="hidden" id="tipo" name="tipo" value="{{$tipo}}"> 
    <input type="hidden" id="flag" name="flag" value="{{$flag}}">
    @if(isset($auxiliar))
        <input type="hidden" id="group" name="group" value="{{$auxiliar}}">
    @endif    
    @if($tipo == 1)

        <div class="form-group">
            <label for="empleado" class="col-lg-3 control-label requerido">Colaboradores:</label>
            <div class="col-lg-8">
                <select multiple style="width: 95%" class="js-example-basic-multiple" name="empleado[]" id="empleado">
                    @if($flag == 0)
                        @foreach($employee as $employee => $index)
                            @if((isset($datas)) == true && $datas->employee_id == $index)
                                <option selected value="{{$index}}">{{$employee}}</option>
                            @else
                                <option value="{{$index}}">{{$employee}}</option>
                            @endif
                        @endforeach
                    @else
                        @foreach($employee as $employee => $index)
                            <?php $seleccion = 0; ?>
                            @for($i = 0 ; count($empleados) > $i ; $i++)
                                @if($empleados[$i]->idEmp == $index)
                                    <option selected value="{{$index}}">{{$employee}}</option>    
                                    $seleccion = 1;
                                @endif
                            @endfor
                            @if($seleccion != 1)
                            <option value="{{$index}}">{{$employee}}</option>    
                            @endif
                        @endforeach
                    @endif
                </select>    
            </div>
        </div>

    @else
        <div class="form-group">
            <label for="departamento" class="col-lg-3 control-label requerido">Departamentos CAP:</label>
            <div class="col-lg-8">              
                <select name="departamento" id="departamento">  
                    <option value=0>Seleccione departamento CAP</option>
                    @foreach($department as $department => $index)
                            @if((isset($datas)) == true && $datas->department_id == $index)
                                <option selected value="{{$index}}">{{$department}}</option>
                            @else
                                <option value="{{$index}}">{{$department}}</option>
                            @endif
                    @endforeach
                </select>
            </div>

        </div>
        
    @endif