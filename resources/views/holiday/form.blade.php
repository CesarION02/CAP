<div class="form-group">
        <label for="nombre" class="col-lg-3 control-label requerido">Nombre:</label>
        <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
        </div>
    </div>
    <div class="form-group">
        <label for="entry" class="col-lg-3 control-label requerido">Fecha:</label>
        <div class="col-lg-8">
            <input type="date" name="fecha" id="fecha" class="form-control" value="{{old('fecha', $data->fecha ?? '')}}" required>
        </div>
    </div>
    <div class="form-group">
        <label for="entry" class="col-lg-3 control-label requerido">Año:</label>
        <div class="col-lg-8">
            <input type="text" name="year" id="year" class="form-control" value="{{old('year', $data->year ?? '')}}" required>
        </div>
    </div>