<div class="form-group">
        <label for="nombre" class="col-lg-3 control-label requerido">Nombre:</label>
        <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
        </div>
    </div>
    <div class="form-group">
        <label for="entry" class="col-lg-3 control-label requerido">Hora Entrada:</label>
        <div class="col-lg-8">
            <input type="time" name="entry" id="entry" class="form-control" value="{{old('entry', $data->entry ?? '')}}" required>
        </div>
    </div>
    <div class="form-group">
        <label for="departure" class="col-lg-3 control-label requerido">Hora Salida:</label>
        <div class="col-lg-8">
            <input type="time" name="departure" id="departure" class="form-control" value="{{old('departure', $data->departure ?? '')}}" required>
        </div>
    </div>
    <div class="form-group">
            <label for="work_time" class="col-lg-3 control-label requerido">Horas trabajar:</label>
            <div class="col-lg-8">
                <input type="number" name="work_time" id="work_time" class="form-control" value="{{old('work_time', $data->work_time ?? '')}}" required>
            </div>
    </div>
    <div class="form-group">
            <label for="overtimepershift" class="col-lg-3 control-label requerido">Horas extra del turno:</label>
            <div class="col-lg-8">
                <input type="number" step="0.5" name="overtimepershift" id="overtimepershift" class="form-control" value="{{old('overtimepershift', $data->overtimepershift ?? '')}}" required>
            </div>
    </div>
    <div class="form-group">
            <label for="order" class="col-lg-3 control-label requerido">Orden(en caso de que roten):</label>
            <div class="col-lg-8">
                <input type="number" name="order" id="order" class="form-control" value="{{old('order', $data->order ?? '')}}" required>
            </div>
    </div>