<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Usuario:</label>
    <div class="col-lg-8">
    <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Username:</label>
    <div class="col-lg-8">
    <input type="text" name="username" id="username" class="form-control" value="{{old('name', $data->username ?? '')}}" required/>
    </div>
</div>
<div class="form-group">
    <label for="email" class="col-lg-3 control-label requerido">Correo:</label>
    <div class="col-lg-8">
        <input type="email" name="email" id="email" class="form-control" value="{{old('email', $data->email ?? '')}}" required>
    </div>
</div>
<div class="form-group">
    <label for="name" class="col-lg-3 control-label requerido">Empleado asociado:</label>
    <div class="col-lg-8">
        @if(isset($datas))
                <select id="employee_id" name="employee_id" class="form-control">
                    <option value="0">N/A</option>
                    @foreach($employees as $employee => $index)
                        @if($datas->employee_id == $index)
                            <option selected value="{{ $index }}"  > {{$employee}}</option>
                        @else
                            <option value="{{ $index }}"  > {{$employee}}</option>
                        @endif
                    @endforeach
                </select>   
            @else
                <select id="employee_id" name="employee_id" class="form-control">
                        <option value="0">N/A</option>
                    @foreach($employees as $employee => $index)
                        <option value="{{ $index }}" {{old('boss_id') == $index ? 'selected' : '' }} > {{$employee}}</option>
                    @endforeach
                </select>
            @endif
    </div>
</div>
<div class="form-group">
    <label for="email" class="col-lg-3 control-label requerido">Contraseña:</label>
    <div class="col-lg-8">
        <input type="text" name="password" id="password" class="form-control" value="{{old('password', $data->password ?? '')}}" required>
    </div>
</div>