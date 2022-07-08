<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Puesto:</label>
    <div class="col-lg-8">
    <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
    </div>
</div>
<div class="form-group">
    <label for="department_id" class="col-lg-3 control-label requerido">Departamento CAP:</label>
    <div class="col-lg-8">
        @if(isset($data))
                <select id="department_id" name="department_id" class="form-control select2-class" required>
                    @foreach($departments as $department => $index)
                        @if($data->department_id == $index)
                            <option selected value="{{ $index }}"  > {{$department}}</option>
                        @else
                            <option value="{{ $index }}"  > {{$department}}</option>
                        @endif
                    @endforeach
                </select>   
            @else
                <select id="department_id" name="department_id" class="form-control select2-class" required>
                    @foreach($departments as $department => $index)
                        <option value="{{ $index }}" {{old('department_id') == $index ? 'selected' : '' }} > {{$department}}</option>
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