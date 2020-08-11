<div class="form-group">
        <label for="nombre" class="col-lg-3 control-label requerido">Nombre empleado:</label>
        <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" readonly/>
        </div>
</div>
<div class="form-group">
        <label for="num_employee" class="col-lg-3 control-label requerido">Número empleado:</label>
        <div class="col-lg-8">
            <input type="number" name="num_employee" id="num_employee" class="form-control" value="{{old('num_employee', $data->num_employee ?? '')}}" readonly>
        </div>
</div>
<div class="form-group">
        <label for="way_register_id" class="col-lg-3 control-label requerido">Manera checar:</label>
        <div class="col-lg-8">
                <select id="way_register_id" name="way_register_id" class="form-control">
                    @foreach($way as $way => $index)
                        <option value="{{ $index }}" {{old('way_register_id') == $index ? 'selected' : '' }} > {{$way}}</option>
                    @endforeach
                </select>
        </div>
</div>


