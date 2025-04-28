<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Codigo de grupo:</label>
    <div class="col-lg-8">
        <input type="text" name="code" id="code" class="form-control" value="{{old('code', $data->code ?? '')}}" required/>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Grupo de departamentos CAP:</label>
    <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
    </div>
</div>
