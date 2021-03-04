@if(isset($year))
<div class="form-group">
    <label for="date" class="col-lg-3 control-label requerido">Año:</label>
    <div class="col-lg-3">
        <select id="anio" name="anio" required>
            <option value="0">Seleccione año</option>
            @for( $i = 0 ; count($year) > $i ; $i++){
              <option value={{$year[$i]->year}}>{{$year[$i]->year}}</option>
            }
            @endfor
        </select>
    </div>  
</div>
@endif
<div class="form-group">
    <label for="festivo" class="col-lg-3 control-label requerido">Día festivo:</label>
    <div class="col-lg-5" id="selectfestivo">
        <select name="festivo" id="festivo">
            <option value="">Seleccione día festivo</option>
            @foreach($holiday as $holiday => $index)
                @if(isset($datas))
                    @if($datas->holiday_id == $index)
                        <option selected value="{{$index}}">{{$holiday}}</option>
                    @endif
                @endif
            @endforeach
        </select>
    </div>
     
</div>
<div class="form-group">
    <label for="date" class="col-lg-3 control-label requerido">Fecha:</label>
    <div class="col-lg-3">
        @if(isset($datas))
            <input type="date" name="date" id="date" value="{{$datas->date}}" >
        @else
            <input type="date" name="date" id="date" disabled>
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
        <label for="empleado" class="col-lg-3 control-label requerido">Colaboradores:</label>
        <div class="col-lg-8">
            <select multiple style="width: 95%" class="js-example-basic-multiple" name="empleado[]" id="empleado">
                
                    @foreach($employee as $employee => $index)
                        @if((isset($datas)) == true && $datas->employee_id == $index)
                            <option selected value="{{$index}}">{{$employee}}</option>
                        @else
                            <option value="{{$index}}">{{$employee}}</option>
                        @endif
                    @endforeach
                
            </select>    
        </div>
    </div>
    @break

    @case(2)
    <div class="form-group">
        <label for="departamento" class="col-lg-3 control-label requerido">Departamento CAP:</label>
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
    
    @break
    @case(3)
    <label for="area" class="col-lg-3 control-label requerido">Área:</label>
    <div class="col-lg-8">              
        <select name="area" id="area">  
            <option value=0>Seleccione área</option>
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