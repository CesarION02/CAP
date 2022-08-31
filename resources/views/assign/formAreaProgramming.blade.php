<div class="form-group">
    <label for="start_date" class="col-lg-3 control-label requerido">Fecha inicial:</label>
    <div class="col-lg-3">
        @if(isset($datas))
            <input type="date" name="start_date" id="start_date" v-model="start_date" value="{{$datas->start_date}}" required>
        @else
            <input type="date" name="start_date" id="start_date" v-model="start_date" required>
        @endif
    </div>
</div> 

<div class="form-group">
    <label for="area" class="col-lg-3 control-label requerido">Areas:</label>
    <div class="col-lg-8">
        <select style="width: 95%" class="js-example-basic-multiple" name="area" id="area" required>
            <option value=""></option>
            @foreach($areas as $area)
                @if(isset($datas))
                    @if ($datas->area_id == $area->id)
                        <option value="{{$datas->area_id}}" selected>{{$area->name}}</option>
                    @else
                        <option value="{{$area->id}}">{{$area->name}}</option>
                    @endif
                @else
                    <option value="{{$area->id}}">{{$area->name}}</option>
                @endif
            @endforeach
        </select>    
    </div>
</div>
<div class="form-group">
    <label for="horario" class="col-lg-3 control-label requerido">Horario:</label>
    <div class="col-lg-4">
        <select name="horario1" class="js-example-basic-multiple" id="horario1" required>
            <option value=""></option>
            @foreach($schedule_template as $schedule_template => $index)
                @if(isset($datas))
                    @if($datas->schedule_template_id == $index)
                        <option selected value="{{$index}}">{{$schedule_template}}</option>
                    @else
                        <option value="{{$index}}">{{$schedule_template}}</option>
                    @endif
                @else
                    <option value="{{$index}}">{{$schedule_template}}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>