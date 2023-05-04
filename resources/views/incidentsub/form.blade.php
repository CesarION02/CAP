<div class="form-group">
    <div class="row">
        <div class="col-md-3 col-md-offset-1">
            <label for="subtype_name">Nombre subtipo:*</label>
        </div>
        <div class="col-md-6">
            <input required maxlength="150" type="text" name="subtype_name" id="subtype_name" class="form-control" 
            placeholder="Subtipo de incidencia" aria-describedby="helpId" value="{{ isset($oSubType) ? $oSubType->name : "" }}">
            <small id="helpId" class="text-muted">Introduzca texto para especificar la incidencia</small>
        </div>
    </div>
</div>

<div class="form-check">
  <label class="form-check-label col-md-offset-4">
    <input type="checkbox" class="form-check-input" name="is_default" id="is_default" value="checkedValue" {{ isset($oSubType) && $oSubType->is_default ? "checked" : "" }}>
    Es el valor por default
  </label>
</div>