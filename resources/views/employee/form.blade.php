<div class="form-group">
        <label for="nombre" class="col-lg-3 control-label requerido">Nombre:</label>
        <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" readonly/>
        </div>
</div>
<div class="form-group">
        <label for="num_employee" class="col-lg-3 control-label requerido">Numero Empleado:</label>
        <div class="col-lg-8">
            <input type="number" name="num_employee" id="num_employee" class="form-control" value="{{old('num_employee', $data->num_employee ?? '')}}" readonly>
        </div>
</div>
<div class="form-group">
        <label for="way_register_id" class="col-lg-3 control-label requerido">Manera checar:</label>
        <div class="col-lg-8">
                <select id="way_register_id" name="way_register_id" class="form-control">
                    @foreach($way as $way => $index)
                        @if(isset($data))
                                @if($index == $data->way_register_id)
                                        <option value="{{ $index }}" selected> {{$way}}</option>       
                                @else
                                        <option value="{{ $index }}" > {{$way}}</option>
                                @endif
                        @else
                                <option value="{{ $index }}" > {{$way}}</option>
                        @endif
                    @endforeach
                </select>
        </div>
</div>
<div class="form-group">
        <label for="is_overtime" class="col-lg-3 control-label requerido">Tiempo extra:</label>
        <div class="col-lg-8">
                <input type="checkbox" value="1" name="is_overtime" id="is_overtime"
                         {{ (isset($data->is_overtime) && $data->is_overtime) ? 'checked="checked" ' : '' }}>
        </div>
</div>
<div class="form-group">
        <label for="department_id" class="col-lg-3 control-label requerido">Departamento CAP:</label>
        <div class="col-lg-8">
                <select id="department_id" name="department_id" class="departamento form-control">
                    @foreach($department as $department => $index)
                        @if(isset($data))
                                @if($index == $data->department_id)
                                        <option value="{{ $index }}" selected> {{$department}}</option>       
                                @else
                                        <option value="{{ $index }}" > {{$department}}</option>
                                @endif
                        @else
                                <option value="{{ $index }}" > {{$department}}</option>
                        @endif
                    @endforeach
                </select>
        </div>
</div>
<div class="form-group">
        <label for="job_id" class="col-lg-3 control-label requerido">Puesto:</label>
        <div class="col-lg-8" id="job" name="job">
                
        </div>
</div>
<div class="form-group">
        <label for="ben_pol_id" class="col-lg-3 control-label requerido">Criterio beneficios:</label>
        <div class="col-lg-8">
                <select id="ben_pol_id" name="ben_pol_id" class="form-control">
                    @foreach($benPols as $bp => $index)
                        @if(isset($data))
                                @if($index == $data->ben_pol_id)
                                        <option value="{{ $index }}" selected> {{$bp}}</option>       
                                @else
                                        <option value="{{ $index }}" > {{$bp}}</option>
                                @endif
                        @else
                                <option value="{{ $index }}" > {{$bp}}</option>
                        @endif
                    @endforeach
                </select>
        </div>
</div>

