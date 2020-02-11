<div class="form-group">
        <label for="nombre" class="col-lg-3 control-label requerido">Nombre:</label>
        <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
        </div>
</div>
<div class="form-group">
        <label for="employee_id" class="col-lg-3 control-label requerido">Area:</label>
        <div class="col-lg-8">
            <select id="area_id" name="area_id" class="form-control">
                @foreach($areas as $area => $index)
                <option value="{{ $index }}" {{old('area_id') == $index ? 'selected' : '' }} > {{$area}}</option>
                @endforeach
            </select>
    
        </div>
</div>