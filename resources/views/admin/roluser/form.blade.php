<div class="form-group">
    <label for="user_id" class="col-lg-3 control-label requerido">Usuario:</label>
    <div class="col-lg-8">
        <select id="usuario_id" name="usuario_id" class="form-control">
            @foreach($users as $user => $index)
            <option value="{{ $index }}" {{old('user_id') == $index ? 'selected' : '' }} > {{$user}}</option>
            @endforeach
        </select>

    </div>
</div>
<div class="form-group">
    <label for="user_id" class="col-lg-3 control-label requerido">Rol:</label>
    <div class="col-lg-8">
        <select id="rol_id" name="rol_id" class="form-control">
            @foreach($rols as $rol => $index)
            <option value="{{ $index }}" {{old('rol_id') == $index ? 'selected' : '' }} > {{$rol}}</option>
            @endforeach
        </select>

    </div>
</div>