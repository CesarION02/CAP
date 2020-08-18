<div class="form-group">
    <label for="num_employee" class="col-lg-3 control-label">Número empleado:</label>
    <div class="col-lg-8">
        <input type="number" name="num_employee" id="num_employee" class="form-control" value="{{old('num_employee', $data->num_employee ?? '')}}" disabled>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label">Empleado:</label>
    <div class="col-lg-8">
    <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" disabled/>
    </div>
</div>
<div class="form-group">
    <label for="short_name" class="col-lg-3 control-label requerido">Nombre corto:</label>
    <div class="col-lg-8">
    <input type="text" name="short_name" id="short_name" class="form-control" value="{{old('short_name', $data->short_name ?? '')}}"/>
    </div>
</div>
<div class="form-group">
    <label for="department_id" class="col-lg-3 control-label requerido">Departamento CAP:</label>
    <div class="col-lg-8">
            <select id="department_id" name="department_id" class="departamento form-control">
                @foreach($departments as $department)
                    @if(isset($data))
                            @if($department->idDep == $data->department_id)
                                    <option value="{{ $department->idDep }}" selected> {{$department->nameDep}}</option>       
                            @else
                                    <option value="{{ $department->idDep }}" > {{$department->nameDep}}</option>
                            @endif
                    @else
                            <option value="{{ $department->idDep }}" > {{$department->nameDep}}</option>
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
    <label for="job_id" class="col-lg-3 control-label readonly">Puesto anterior:</label>
    <div class="col-lg-8" id="jobaux" name="jobaux">
        <input type="text" name="jobanterior" id="jobanterior" value="{{$data->job->name}}">       
    </div>
</div>




