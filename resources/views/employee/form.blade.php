<div class="form-group">
        <label for="nombre" class="col-lg-3 control-label">Nombre {{ (isset($becario) && $becario ? 'practicante' : 'empleado') }}:</label>
        <div class="col-lg-8">
                <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" {{ isset($data) ? "readonly" : "" }}>
        </div>
</div>
@if(isset($becario))
        <input type="hidden" name="becario" id="becario" value="{{$becario}}">
@else
        <input type="hidden" name="becario" id="becario" value="0">
@endif

<div class="form-group">
        <label for="num_employee" class="col-lg-3 control-label">Número {{ (isset($becario) && $becario ? 'practicante' : 'empleado') }}:</label>
        <div class="col-lg-8">
            <input type="number" name="num_employee" id="num_employee" class="form-control" value="{{ (isset($numColl) && $numColl > 0) ? $numColl : old('num_employee', $data->num_employee ?? '')}}" {{ isset($data) ? "readonly" : "" }}>
        </div>
</div>
<div class="form-group">
        <label for="way_register_id" class="col-lg-3 control-label requerido">Política registro:</label>
        <div class="col-lg-8">
                <select id="way_register_id" name="way_register_id" class="form-control select2-class">
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
                <select id="policy_id" name="policy_id" class="form-control select2-class">
                        @foreach($policy as $policy => $index)
                            @if(isset($data))
                                    @if($index == $data->policy_extratime_id)
                                            <option value="{{ $index }}" selected> {{$policy}}</option>       
                                    @else
                                            <option value="{{ $index }}" > {{$policy}}</option>
                                    @endif
                            @else
                                    <option value="{{ $index }}" > {{$policy}}</option>
                            @endif
                        @endforeach
                    </select>
        </div>
</div>
<div class="form-group">
        <label for="department_id" class="col-lg-3 control-label requerido">Departamento CAP:</label>
        <div class="col-lg-8">
                <select id="department_id" name="department_id" class="departamento form-control select2-class">
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
        <label for="job_id" class="col-lg-3 control-label requerido">Puesto CAP:</label>
        <div class="col-lg-8" id="job" name="job">
                
        </div>
</div>
<div class="form-group">
        <label for="cambio" class="col-lg-3 control-label">Mantener depto y puesto:</label>
        <div class="col-lg-6">
                @if(isset($data))
                        @if( $data->lock_depto == 1 )
                                <input type="checkbox" checked id="cambio" name="cambio"> 
                        @else
                                <input type="checkbox" id="cambio" name="cambio"> 
                        @endif
                @else
                        <input type="checkbox" id="cambio" name="cambio"> 
                @endif
                
        </div>
</div>
<div class="form-group">
        <label for="dept_rh_id" class="col-lg-3 control-label">Departamento nominas:</label>
        <div class="col-lg-8">
                <input type="text" name="dept_rh_id" id="dept_rh_id" class="form-control" value="{{$dept_rh}}" readonly>
        </div>
</div>
<div class="form-group">
        <label for="job_rh_id" class="col-lg-3 control-label">Puesto nominas:</label>
        <div class="col-lg-8">
                <input type="text" name="job_rh_id" id="job_rh_id" class="form-control" value="{{$job_rh}}" readonly>
        </div>
</div>
<div class="form-group">
        <label for="ben_pol_id" class="col-lg-3 control-label requerido">Criterio beneficios:</label>
        <div class="col-lg-8">
                <select id="ben_pol_id" name="ben_pol_id" class="form-control select2-class">
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

