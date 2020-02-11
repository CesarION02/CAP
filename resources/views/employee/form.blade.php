<div class="form-group">
        <label for="nombre" class="col-lg-3 control-label requerido">Nombre:</label>
        <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
        </div>
</div>
<div class="form-group">
        <label for="num_employee" class="col-lg-3 control-label requerido">Numero Empleado:</label>
        <div class="col-lg-8">
            <input type="number" name="num_employee" id="num_employee" class="form-control" value="{{old('num_employee', $data->num_employee ?? '')}}" required>
        </div>
</div>
<div class="form-group">
        <label for="nip" class="col-lg-3 control-label requerido">Nip:</label>
        <div class="col-lg-8">
            <input type="number" name="nip" id="nip" class="form-control" value="{{old('departure', $data->nip?? '')}}" required>
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
<div class="form-group">
        <label for="job_id" class="col-lg-3 control-label requerido">Puesto:</label>
        <div class="col-lg-8">
                <select id="job_id" name="job_id" class="form-control">
                    @foreach($job as $job => $index)
                        <option value="{{ $index }}" {{old('job_id') == $index ? 'selected' : '' }} > {{$job}}</option>
                    @endforeach
                </select>
        </div>
</div>

