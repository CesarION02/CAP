<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Usuario:</label>
    <div class="col-lg-8">
        <input type="text" name="name" id="name" value="{{$data->name}}"  class="form-control" required/>
    </div>
</div>
<div class="form-group">
    <label for="email" class="col-lg-3 control-label requerido">Correo:</label>
    <div class="col-lg-8">
        <input type="email" name="email" id="email" class="form-control" value="{{$data->email}}" required>
    </div>
</div>
<div class="form-group">
    <label for="empleado" class="col-lg-3 control-label requerido">Empleado asociado:*</label>
    <div class="col-lg-8">
        <select id="employee_id" name="employee_id" class="form-control" required>
                <option value="0">N/A</option>
            @foreach($employees as $employee => $index)
                <option value="{{ $index }}"> {{$employee}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Contraseña anterior:*</label>
    <div class="col-lg-8">
    <input type="text" name="prevpass" id="prevpass" class="form-control"  required/>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Contraseña nueva:*</label>
    <div class="col-lg-8">
    <input type="text" name="newpass" id="newpass" class="form-control" required/>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Confirmar contraseña nueva:*</label>
    <div class="col-lg-8">
    <input type="text" name="confirmpass" id="
    " class="form-control" required/>
    </div>
</div>
<input type="hidden" name="rol" id="rol" value="{{$rol->rol_id}}">