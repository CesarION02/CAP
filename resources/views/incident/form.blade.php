<div class="form-group">
    <label for="Tipoincidente" class="col-lg-3 control-label requerido">Tipo incidencia:</label>
    <div class="col-lg-8">
        <select id="type_incidents_id" name="type_incidents_id" class="form-control">
            @foreach($incidents as $type => $index)
            <option value="{{ $index }}" {{old('type_incidents_id') == $index ? 'selected' : '' }}> {{$type}}</option>
            @endforeach
        </select>

    </div>
</div>
<div class="form-group">
    <label for="start_date" class="col-lg-3 control-label requerido">Fecha inicial:</label>
    <div class="col-lg-8">
        <input type="date" class="form-control" name="start_date" id="start_date" value="{{old('start_date', $data->start_date ?? '')}}" class="form-control">
    </div>
</div>
<div class="form-group">
        <label for="end_date" class="col-lg-3 control-label requerido">Fecha final:</label>
        <div class="col-lg-8">
            <input type="date" class="form-control" name="end_date" id="end_date" value="{{old('start_date', $data->end_date ?? '')}}" class="form-control">
        </div>
</div>
<div class="form-group">
        <label for="employee_id" class="col-lg-3 control-label requerido">Empleado:</label>
        <div class="col-lg-8">
            <select id="employee_id"  name="employee_id" class="form-control">
                @foreach($employees as $employee => $index)
                <option value="{{ $index }}" {{old('employee_id') == $index ? 'selected' : '' }}> {{$employee}}</option>
                @endforeach
            </select>
    
        </div>
</div>