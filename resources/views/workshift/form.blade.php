<div class="form-group">
        <label for="nombre" class="col-lg-3 control-label requerido">Turno:</label>
        <div class="col-lg-8">
        <input type="text" name="name" id="name" class="form-control" value="{{old('name', $data->name ?? '')}}" required/>
        </div>
    </div>
    <div class="form-group">
        <label for="entry" class="col-lg-3 control-label requerido">Hr entrada:</label>
        <div class="col-lg-8">
            <input type="time" name="entry" id="entry" class="form-control" value="{{old('entry', $data->entry ?? '')}}" required>
        </div>
    </div>
    <div class="form-group">
        <label for="departure" class="col-lg-3 control-label requerido">Hr salida:</label>
        <div class="col-lg-8">
            <input type="time" name="departure" id="departure" class="form-control" value="{{old('departure', $data->departure ?? '')}}" required>
        </div>
    </div>
    <div class="form-group">
            <label for="work_time" class="col-lg-3 control-label requerido">Horas jornada:</label>
            <div class="col-lg-8">
                <input type="number" name="work_time" id="work_time" class="form-control" value="{{old('work_time', $data->work_time ?? '')}}" required>
            </div>
    </div>
    <div class="form-group">
            <label for="overtimepershift" class="col-lg-3 control-label">Horas extra:</label>
            <div class="col-lg-8">
                <input type="number" step="0.5" name="overtimepershift" id="overtimepershift" class="form-control" value="{{old('overtimepershift', $data->overtimepershift ?? '')}}" required>
            </div>
    </div>
    <div class="form-group">
        <label for="cut_id" class="col-lg-3 control-label requerido">Política recorte:</label>
        <div class="col-lg-8">
            <select id="cut_id" name="cut_id" class="form-control">
                @foreach($datas as $data => $index)
                <option value="{{ $index }}" {{old('cut_id') == $index ? 'selected' : '' }} > {{$data}}</option>
                @endforeach
            </select>
    
        </div>
    </div>
    <div class="form-group">
            <label for="order" class="col-lg-3 control-label">Posición rotación:</label>
            <div class="col-lg-8">
                <input type="number" name="order" id="order" class="form-control" value="{{old('order', $data->order ?? '')}}" required>
            </div>
    </div>