<div class="form-group">
    <label for="festivo" class="col-lg-3 control-label">Día festivo:</label>
    <div class="col-lg-3">
        <select name="festivo" id="festivo">
            @foreach($holiday as $holiday => $index)
                @if(isset($datas))
                    @if($datas->holiday_id == $index)
                        <option selected value="{{$index}}">{{$holiday}}</option>
                    @else
                        <option value="{{$index}}">{{$holiday}}</option>
                    @endif
                @else
                    <option value="{{$index}}">{{$holiday}}</option>
                @endif
            @endforeach
        </select>
    </div>
    <label for="date" class="col-lg-3 control-label">Fecha:</label>
    <div class="col-lg-3">
        @if(isset($datas))
            <input type="date" name="date" id="date" value="{{$datas->date}}" >
        @else
            <input type="date" name="date" id="date">
        @endif
    </div>  
</div>
<input type="hidden" id="tipo" name="tipo" value="{{$tipo}}"> 
@if(isset($auxiliar))
    <input type="hidden" id="group" name="group" value="{{$auxiliar}}">
@endif    
@switch($tipo)
    @case(1)
    <div class="form-group">
        <label for="empleado" class="col-lg-3 control-label">Empleados:</label>
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
    @break

    @case(2)
    <div class="form-group">
        <label for="departamento" class="col-lg-3 control-label">Departamento:</label>
        <div class="col-lg-8">              
            <select name="departamento" id="departamento">  
                <option value=0>Seleccionar Departamento</option>
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
    
    @break
    @case(3)
    <label for="area" class="col-lg-3 control-label">Área:</label>
    <div class="col-lg-8">              
        <select name="area" id="area">  
            <option value=0>Seleccionar Área</option>
            @foreach($area as $area => $index)
                    @if((isset($datas)) == true && $datas->area_id == $index)
                        <option selected value="{{$index}}">{{$area}}</option>
                    @else
                        <option value="{{$index}}">{{$area}}</option>
                    @endif
            @endforeach
        </select>
    </div>

    @break
@endswitch