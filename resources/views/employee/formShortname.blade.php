<div class="form-group">
    <label for="num_employee" class="col-lg-3 control-label">Numero Empleado:</label>
    <div class="col-lg-8">
        <input type="number" name="num_employee" id="num_employee" class="form-control" value="{{old('num_employee', $data->num_employee ?? '')}}" disabled>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label">Nombre:</label>
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




