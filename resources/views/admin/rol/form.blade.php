<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Rol:</label>
    <div class="col-lg-8">
    <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
    </div>
</div>