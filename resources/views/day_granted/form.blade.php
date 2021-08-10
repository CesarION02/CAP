
<div class="form-group">
    <label for="start_date" class="col-lg-3 control-label requerido">Fecha:</label>
    <div class="col-lg-8">
        <input type="date" class="form-control" name="start_date" id="start_date" value="{{old('start_date', $data->date ?? '')}}" class="form-control">
    </div>
</div>
<div class="form-group">
        <label for="employee_id" class="col-lg-3 control-label requerido">Colaborador:</label>
        <div class="col-lg-8">
            <input type="text" value="{{ $datas[0]->nameEmp }}" class="form-control" readonly>
        </div>
</div>
<div class="form-group">
    <label for="employee_id" class="col-lg-3 control-label requerido">Día festivo:</label>
    <div class="col-lg-8">
        <input type="text" value="{{ $datas[0]->nameholi }}" class="form-control" readonly>
    </div>
</div>
<div class="form-group">
    <label for="employee_id" class="col-lg-3 control-label requerido">Comentarios:</label>
    <div class="col-lg-8">
        <input type="text" value="{{ $datas[0]->comentarios }}" class="form-control" name="comment" id="comment">
    </div>
</div>