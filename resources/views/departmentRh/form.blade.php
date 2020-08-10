<div class="form-group">
    <label for="employee_id" class="col-lg-3 control-label requerido">Departamento CAP predeterminado:</label>
    <div class="col-lg-8">
        @if(isset($data))
            <select id="department_id" name="department_id" class="form-control">
                @foreach($departments as $department => $index)
                    @if($data->default_dept_id == $index)
                        <option selected value="{{ $index }}"  > {{$department}}</option>
                    @else
                        <option value="{{ $index }}"  > {{$department}}</option>
                    @endif
                @endforeach
            </select>
        @endif
    </div>
</div>