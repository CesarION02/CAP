<div  class="form-group">
    <label for="colaborador" class="col-lg-3 control-label requerido">Colaborador:*</label>
    <div class="col-lg-8">
        <select id="colaborador" name="colaborador" class="form-control" required>
            <option value="0">N/A</option>
            @foreach($uGlobales as $ug)
                <option value="{{ $ug->id_global_user }}"> {{$ug->full_name}}</option>
            @endforeach
    </select>
    </div>
</div>
<input type="hidden" name="global" id="global" value="{{json_encode($uGlobales)}}">
<div  class="form-group">
    <div class="col-lg-3"></div>
    <div class="col-lg-6">
        <button type="button" class="btn btn-primary" id="seleccionar" onclick="llenar()">Seleccionar</button>
    </div>
</div>
<br>
<br>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Usuario:</label>
    <div class="col-lg-8">
    <input type="text" name="name" id="name" class="form-control" disabled/>
    </div>
    <input type="hidden" name="fname" id="fname">
</div>
<div class="form-group">
    <label for="email" class="col-lg-3 control-label requerido">Correo:</label>
    <div class="col-lg-8">
        <input type="email" name="email" id="email" class="form-control" required disabled>
    </div>
</div>
<div class="form-group">
    <label for="empleado" class="col-lg-3 control-label requerido">Empleado asociado:</label>
    <div class="col-lg-8">
        <select id="employee_id" name="employee_id" class="form-control" disabled>
                <option value="0">N/A</option>
            @foreach($employees as $employee => $index)
                <option value="{{ $index }}"> {{$employee}}</option>
            @endforeach
        </select>
    </div>
    <input type="hidden" name="femployee_id" id="femployee_id">
    <input type="hidden" name="fglobal" id="fglobal">
</div>
<div class="form-group">
    <input type="hidden" name="fpassword" id="fpassword">
</div>