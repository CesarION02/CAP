<div class="form-group">
    <label for="name" class="col-lg-3 control-label requerido">Área:</label>
    <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" />
    </div>
</div>
<div class="form-group">
    <label for="name" class="col-lg-3 control-label requerido">Encargado:</label>
    <div class="col-lg-8">
        @if(isset($data))
                <select id="boss_id" name="boss_id" class="form-control">
                    @foreach($employees as $employee => $index)
                        @if($data->boss_id == $index)
                            <option selected value="{{ $index }}"  > {{$employee}}</option>
                        @else
                            <option value="{{ $index }}"  > {{$employee}}</option>
                        @endif
                    @endforeach
                </select>   
            @else
                <select id="boss_id" name="boss_id" class="form-control">
                    @foreach($employees as $employee => $index)
                        <option value="{{ $index }}" {{old('boss_id') == $index ? 'selected' : '' }} > {{$employee}}</option>
                    @endforeach
                </select>
            @endif
    </div>
</div>

