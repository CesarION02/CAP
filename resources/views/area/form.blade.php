<div class="form-group">
    <label for="name" class="col-lg-3 control-label requerido">Área:</label>
    <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
    </div>
</div>
<div class="form-group">
    <label for="name" class="col-lg-3 control-label requerido">Encargado:</label>
    <div class="col-lg-8">
        @if(isset($data))
                <select id="boss_id" name="boss_id" class="form-control select2-class" required>
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
