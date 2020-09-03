
<div class="form-group">
    <label for="start_date" class="col-lg-3 control-label requerido">Semana:</label>
    <div class="col-lg-3">
        <input type="week" name="semana" id="semana" required>
    </div>
    <p>
        <b>Nota</b>: Este control no está soportado en Firefox, Safari o Internet Explorer 11 (o anterior).
    </p>
</div>
<div class="form-group">
    <label for="empleado" class="col-lg-3 control-label requerido">Empleados:</label>
    <div class="col-lg-8">
        <select multiple style="width: 95%" class="js-example-basic-multiple" name="empleado[]" id="empleado" required>
                @foreach($employees as $employee )
                    <option value="{{$employee->id}}">{{$employee->name}}</option>
                @endforeach
        </select>    
    </div>
</div>