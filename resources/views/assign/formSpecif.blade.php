
<div class="form-group">
    <label for="start_date" class="col-lg-3 control-label">Semana:</label>
    <div class="col-lg-3">
        <input type="week" name="semana" id="semana" required>
    </div>  
</div>
<div class="form-group">
    <label for="empleado" class="col-lg-3 control-label">Empleados:</label>
    <div class="col-lg-8">
        <select multiple style="width: 95%" class="js-example-basic-multiple" name="empleado[]" id="empleado">
                @foreach($employees as $employee )
                    <option value="{{$employee->id}}">{{$employee->name}}</option>
                @endforeach
        </select>    
    </div>
</div>