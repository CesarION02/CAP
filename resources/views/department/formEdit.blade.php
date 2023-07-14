<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Departamento CAP:</label>
    <div class="col-lg-8">
    <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
    </div>
</div>
<div class="form-group">
    <label for="employee_id" class="col-lg-3 control-label requerido">Área:</label>
    <div class="col-lg-8">
        @if(isset($data))
            <select id="area_id" name="area_id" class="form-control select2-class" required>
                @foreach($areas as $area => $index)
                    @if($data->area_id == $index)
                        <option selected value="{{ $index }}"  > {{$area}}</option>
                    @else
                        <option value="{{ $index }}"  > {{$area}}</option>
                    @endif
                @endforeach
            </select>   
        @else
            <select id="area_id" name="area_id" class="form-control select2-class" required>
                @foreach($areas as $area => $index)
                    <option value="{{ $index }}" {{old('area_id') == $index ? 'selected' : '' }} > {{$area}}</option>
                @endforeach
            </select>
        @endif
    </div>
</div>
<div class="form-group">
<label for="employee_id" class="col-lg-3 control-label requerido">Departamento nóminas:</label>
<div class="col-lg-8">
    @if(isset($data))
        <select id="rh_department_id" name="rh_department_id" class="form-control select2-class" required>
            @foreach($deptrhs as $deptrh => $index)
                @if($data->rh_department_id == $index)
                    <option selected value="{{ $index }}"  > {{$deptrh}}</option>
                @else
                    <option value="{{ $index }}"  > {{$deptrh}}</option>
                @endif
            @endforeach
        </select>
    @else
        <select id="rh_department_id" name="rh_department_id" class="form-control select2-class" required>
            @foreach($deptrhs as $deptrh => $index)
                <option value="{{ $index }}" {{old('rh_department_id') == $index ? 'selected' : '' }} > {{$deptrh}}</option>
            @endforeach
        </select>
    @endif
</div>
</div>
<div class="form-group">
    <label for="employee_id" class="col-lg-3 control-label requerido">Grupo departamento:</label>
    <div class="col-lg-8">
        
        @if(isset($data))
            <select id="group_dept_id" name="group_dept_id" class="form-control select2-class" required>
                @foreach($groupDept as $group)
                    @if($data->dept_group_id == $group->id)
                        <option selected value="{{ $group->id }}"  > {{$group->name}}</option>
                    @else
                        <option value="{{ $group->id }}"  > {{$group->name}}</option>
                    @endif
                @endforeach
            </select>   
        @else
            <select id="group_dept_id" name="group_dept_id" class="form-control select2-class" required>
                @foreach($groupDept as $group)
                            <option selected value="{{ $group->id }}"  > {{$group->name}}</option>
                @endforeach
            </select>
        @endif
    </div>
</div>
<div class="form-group">
<label for="name" class="col-lg-3 control-label requerido">Encargado:</label>
<div class="col-lg-8">
    @if(isset($data))
            <select id="boss_id" name="boss_id" class="form-control select2-class" required>
                <option>Seleccione encargado</option>
                @foreach($employees as $employee => $index)
                
                    @if($data->boss_id == $index)
                        <option selected value="{{ $index }}"  > {{$employee}}</option>
                    @else
                        <option value="{{ $index }}"  > {{$employee}}</option>
                    @endif
                @endforeach
            </select>   
        @else
            <select id="boss_id" name="boss_id" class="form-control select2-class" required>
                    <option>Seleccione encargado</option>
                @foreach($employees as $employee => $index)
                    <option value="{{ $index }}" {{old('boss_id') == $index ? 'selected' : '' }} > {{$employee}}</option>
                @endforeach
            </select>
        @endif
</div>
</div>
<div class="form-group">
<label for="name" class="col-lg-3 control-label requerido">Politica día festivo:</label>
<div class="col-lg-8">
    @if(isset($data))
            <select id="policy_holiday_id" name="policy_holiday_id" class="form-control select2-class" required>
                @foreach($policyh as $policy => $index)
                    @if($data->policy_holiday == $index)
                        <option selected value="{{ $index }}"  > {{$policy}}</option>
                    @else
                        <option value="{{ $index }}"  > {{$policy}}</option>
                    @endif
                @endforeach
            </select>   
        @else
            <select id="policy_holiday_id" name="policy_holiday_id" class="form-control select2-class" required>
                @foreach($policyh as $policy => $index)
                    <option value="{{ $index }}" {{old('policy_holiday') == $index ? 'selected' : '' }} > {{$policy}}</option>
                @endforeach
            </select>
        @endif
</div>
</div>