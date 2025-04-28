<div class="form-group row">
    <label for="usuario" class="col-lg-2 col-form-label">Puesto nómina:</label>
    <div class="col-lg-3">
        <input type="text" class="form-control" name="usuario" id="usuario" value="{{ $datas[0]->job }}" disabled>
        <input type="hidden" name="job" id="job" value="{{ $datas[0]->id }}">
    </div>
</div>

<div class="form-group row">
    <label for="dg" class="col-lg-2 col-form-label">Grupos departamentos CAP:</label>
    <div class="col-lg-4">
        <select class="chosen-select form-control" id="dg" name="dg[]" multiple data-placeholder="Selecciona grupos de departamentos">
            @foreach($group_departments as $group)
                <option value="{{ $group->id }}" {{ in_array($group->id, $seleccionadosDG) ? 'selected' : '' }}>
                    {{ $group->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-group row">
    <label for="pg" class="col-lg-2 col-form-label">Grupos prenómina:</label>
    <div class="col-lg-4">
        <select class="chosen-select form-control" id="pg" name="pg[]" multiple data-placeholder="Selecciona grupos de prenómina">
            @foreach($group_prepayroll as $group)
                <option value="{{ $group->id_group }}" {{ in_array($group->id_group, $seleccionadosPG) ? 'selected' : '' }}>
                    {{ $group->group_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>