<div class="form-group">
    <label for="nombre" class="col-lg-5 control-label">Día de inicio de semana:</label>
    <div class="col-lg-2">
        <input type="text" name="startOfWeek" id="startOfWeek" class="form-control" value="{{$config->startOfWeek}}"/>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-5 control-label">Minutos de tolerancia:</label>
    <div class="col-lg-2">
        <input type="text" class="form-control" value="{{$config->toleranceMinutes}}" readonly/>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-5 control-label">Minutos para busqueda de checada un día antes o después:</label>
    <div class="col-lg-2">
        <input type="text" class="form-control" value="{{$config->maxGapMinutes}}" />
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-5 control-label">Minutos de holgura para encajonar checadas en un horario:</label>
    <div class="col-lg-2">
        <input type="text" class="form-control" value="{{$config->maxGapSchedule}}" />
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-5 control-label">Minutos tomados en cuenta para poner la leyenda revisar horario</label>
    <div class="col-lg-2">
        <input type="text" class="form-control" value="{{$config->maxGapCheckSchedule}}" />
    </div>
</div>
<div>
    <label></label>
    <div></div>
</div>