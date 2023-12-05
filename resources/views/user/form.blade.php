<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Usuario:</label>
    <div class="col-lg-8">
    <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
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
        @if(isset($data))
                <select id="employee_id" name="employee_id" class="form-control" required>
                    <option value="0">N/A</option>
                    @foreach($employees as $employee => $index)
                        @if($data->employee_id == $index)
                            <option selected value="{{ $index }}"  > {{$employee}}</option>
                        @else
                            <option value="{{ $index }}"  > {{$employee}}</option>
                        @endif
                    @endforeach
                </select>   
            @else
                <select id="employee_id" name="employee_id" class="form-control" required>
                        <option value="0">N/A</option>
                    @foreach($employees as $employee => $index)
                        <option value="{{ $index }}" {{old('boss_id') == $index ? 'selected' : '' }} > {{$employee}}</option>
                    @endforeach
                </select>
            @endif
    </div>
</div>
@if( $type == 1)
    <div class="form-group">
        <label for="email" class="col-lg-3 control-label requerido">Contraseña:</label>
        <div class="col-lg-8">
            <input type="text" name="password" id="password" class="form-control" required>
        </div>
    </div>
    <div class="form-group">
        <label for="email" class="col-lg-3 control-label requerido">Contraseña nuevamente:</label>
        <div class="col-lg-8">
            <input type="text" name="passwordnu" id="passwordnu" class="form-control" required>
        </div>
    </div>
@else
    <div class="form-group">
        <label for="email" class="col-lg-3 control-label"> Cambiar contraseña </label>
        <div class="col-lg-8"> 
            <input type="checkbox" onChange="contrasena()" id="contrasenia" name="contrasenia" value="cambiar" >
        </div>  
    </div>
    <input type="hidden" name="con" id="con" value="0">
    <div class="form-group">
        <label for="email" class="col-lg-3 control-label requerido">Contraseña:</label>
        <div class="col-lg-8">
            <input type="text" name="password" id="password" class="form-control" required disabled>
        </div>
    </div>
    <div class="form-group">
        <label for="email" class="col-lg-3 control-label requerido">Contraseña nuevamente:</label>
        <div class="col-lg-8">
            <input type="text" name="passwordnu" id="passwordnu" class="form-control" required disabled>
        </div>
    </div>
@endif