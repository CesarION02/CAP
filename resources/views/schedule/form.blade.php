<div class="form-group">
        <label for="nombre" class="col-lg-3 control-label">Nombre Plantilla:</label>
        <div class="col-lg-6">
            <input type="text" name="name" id="name" class="form-control" required value=<?php if(isset($datas)){ echo $datas[0]->Name;}else{echo " ";} ?> >
        </div>
        
</div>
<div class="form-group">
    <label for="overtimepershift" class="col-lg-3 control-label">Tiempo extra:</label>
    <div class="col-lg-6">
        <input type="number" step="0.5" name="overtimepershift" id="overtimepershift" class="form-control" required value=<?php if(isset($datas)){ echo $datas[0]->overtimepershift;}else{echo " ";} ?> >
    </div>
    
</div>
<div class="form-group">
    <label for="cut_id" class="col-lg-3 control-label requerido">Recortar Reporte secretaria:</label>
    <div class="col-lg-6">
        <select id="cut_id" name="cut_id" class="form-control">
            @foreach($cuts as $cut => $index)
            <option value="{{ $index }}" {{old('cut_id') == $index ? 'selected' : '' }} > {{$cut}}</option>
            @endforeach
        </select>

    </div>
</div>
<div class="form-group">
        <label for="lunes" class="col-lg-2 control-label">Lunes:</label>
        <div class="col-md-2">
            <input type="time" name="lunesE" id="lunesE" class="form-control" value=<?php if(isset($datas)){ echo $datas[0]->entry;}else{echo " ";}?> >
        </div>
        <div class="col-md-2">
                <input type="time" name="lunesS" id="lunesS" class="form-control" value=<?php if(isset($datas)){ echo $datas[0]->departure;}else{echo " ";}?> >
            </div>
        <div class="col-md-2">
            <input type="checkbox" class="check" name="checklunes" id="checklunes" value="1">Desactivar
        </div>
        
</div>
<div class="form-group">
        <label for="martes" class="col-lg-2 control-label">Martes:</label>
        <div class="col-md-2">
            <input type="time" name="martesE" id="martesE" class="form-control" value=<?php if(isset($datas)){ echo $datas[1]->entry;}else{echo " ";}?> >
        </div>
        <div class="col-md-2">
                <input type="time" name="martesS" id="martesS" class="form-control" value=<?php if(isset($datas)){ echo $datas[1]->departure;}else{echo " ";}?> >
            </div>
        <div class="col-md-2">
            <input type="checkbox" class="check" name="checkmartes" id="checkmartes" value="2">Desactivar
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" onclick="copiar(2)"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>Copiar Anterior</button>
        </div>
</div>
<div class="form-group">
        <label for="miercoles" class="col-lg-2 control-label">Miercoles:</label>
        <div class="col-md-2">
            <input type="time" name="miercolesE" id="miercolesE" class="form-control" value=<?php if(isset($datas)){ echo $datas[2]->entry;}else{echo " ";}?> >
        </div>
        <div class="col-md-2">
            <input type="time" name="miercolesS" id="miercolesS" class="form-control" value=<?php if(isset($datas)){ echo $datas[2]->departure;}else{echo " ";}?>>
        </div>
        <div class="col-md-2">
            <input type="checkbox" class="check" name="checkmiercoles" id="checkmiercoles" value="3">Desactivar
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" onclick="copiar(3)"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>Copiar Anterior</button>
        </div>
</div>
<div class="form-group">
        <label for="jueves" class="col-lg-2 control-label">Jueves:</label>
        <div class="col-md-2">
            <input type="time" name="juevesE" id="juevesE" class="form-control" value=<?php if(isset($datas)){ echo $datas[3]->entry;}else{echo " ";}?>>
        </div>
        <div class="col-md-2">
            <input type="time" name="juevesS" id="juevesS" class="form-control" value=<?php if(isset($datas)){ echo $datas[3]->departure;}else{echo " ";}?>>
        </div>
        <div class="col-md-2">
            <input type="checkbox" class="check" name="checkjueves" id="checkjueves" value="4">Desactivar
        </div>
        <div class="col-md-2">
            <button type="button"  class="btn btn-primary" onclick="copiar(4)"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>Copiar Anterior</button>
        </div>
</div>
<div class="form-group">
        <label for="viernes" class="col-lg-2 control-label">Viernes:</label>
        <div class="col-md-2">
            <input type="time" name="viernesE" id="viernesE" class="form-control" value=<?php if(isset($datas)){ echo $datas[4]->entry;}else{echo " ";}?> >
        </div>
        <div class="col-md-2">
            <input type="time" name="viernesS" id="viernesS" class="form-control" value=<?php if(isset($datas)){ echo $datas[4]->departure;}else{echo " ";}?> >
        </div>
        <div class="col-md-2">
            <input type="checkbox" class="check" name="checkviernes" id="checkviernes" value="5">Desactivar
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" onclick="copiar(5)"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>Copiar Anterior</button>
        </div>
</div>
<div class="form-group">
        <label for="sabado" class="col-lg-2 control-label">Sabado:</label>
        <div class="col-md-2">
            <input type="time" name="sabadoE" id="sabadoE" class="form-control" value=<?php if(isset($datas)){ echo $datas[5]->entry;}else{echo " ";}?> >
        </div>
        <div class="col-md-2">
            <input type="time" name="sabadoS" id="sabadoS" class="form-control" value=<?php if(isset($datas)){ echo $datas[5]->departure;}else{echo " ";}?> >
        </div>
        <div class="col-md-2">
            <input type="checkbox" class="check" name="checksabado" id="checksabado" value="6">Desactivar
        </div>
        <div class="col-md-2">
                <button type="button" class="btn btn-primary" onclick="copiar(6)"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>Copiar Anterior</button>
        </div>
</div>
<div class="form-group">
        <label for="domingo" class="col-lg-2 control-label">Domingo:</label>
        <div class="col-md-2">
            <input type="time" name="domingoE" id="domingoE" class="form-control" value=<?php if(isset($datas)){ echo $datas[6]->entry;}else{echo " ";}?> >
        </div>
        <div class="col-md-2">
                <input type="time" name="domingoS" id="domingoS" class="form-control" value=<?php if(isset($datas)){ echo $datas[6]->departure;}else{echo " ";}?> >
            </div>
        <div class="col-md-2">
                <input type="checkbox"  class="check" name="checkdomingo" id="checkdomingo" value="7">Desactivar
        </div>
        <div class="col-md-2">
                <button type="button" class="btn btn-primary" onclick="copiar(7)"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span>Copiar Anterior</button>
        </div>
</div>