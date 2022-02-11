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
        
        @if(isset($datas))
            <input type="date" class="form-control" name="start_date" id="start_date" value="{{$datas[0]->ini}}" class="form-control">
        @else
            <input type="date" class="form-control" name="start_date" id="start_date" value="" class="form-control">
        @endif
    </div>
</div>
<div class="form-group">
        <label for="end_date" class="col-lg-3 control-label requerido">Fecha final:</label>
        <div class="col-lg-8">
            @if(isset($datas))
                <input type="date" class="form-control" name="end_date" id="end_date" value="{{$datas[0]->fin}}" class="form-control">
            @else
                <input type="date" class="form-control" name="end_date" id="end_date" value="" class="form-control">
            @endif
        </div>
</div>
<div class="form-group">
        <label for="employee_id" class="col-lg-3 control-label requerido">Empleado:</label>
        <div class="col-lg-8">
            @if(isset($datas))
               <input type="text" class="form-control" value="{{$datas[0]->name}}" readonly>
            @else
                <select id="employee_id"  name="employee_id" class="form-control">
                    @foreach($employees as $employee)
                    <option value="{{ $employee->num }}" {{old('employee_id') == $index ? 'selected' : '' }}> {{$employee->name}}</option>
                    @endforeach
                </select>
            @endif
        </div>
</div>