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
    <label for="email" class="col-lg-3 control-label requerido">Contraseña:</label>
    <div class="col-lg-8">
        <input type="password" name="password" id="password" class="form-control" value="{{old('password', $data->password ?? '')}}" required>
    </div>
</div>