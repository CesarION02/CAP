<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="form-group">
            <label for="since_date">A partir de:</label>
            <input type="date" class="form-control" name="since_date" value="{{ isset($oCfg) ? $oCfg->since_date : date('Y-m-d') }}" id="since_date">
        </div>
        <div class="form-group">
            <label for="since_date">Concluye en:</label>
            <input type="date" class="form-control" name="until_date" value="{{ isset($oCfg) ? $oCfg->until : "" }}" id="until_date">
        </div>
        <div class="form-group">
            <label for="type_pay">Tipo</label>
            <select class="form-control" id="type_pay" name="type_pay">
                <option value="1" {{ isset($oCfg) && $oCfg->is_week ? 'selected' : '' }}>Semana</option>
                <option value="2" {{ isset($oCfg) && $oCfg->is_biweek ? 'selected' : '' }}>Quincena</option>
            </select>
        </div>
        <div class="form-group">
            <label for="order_vobo">Orden de jerarquía</label>
            <input type="number" class="form-control" id="order_vobo" name="order_vobo" value="{{ isset($oCfg) ? $oCfg->order_vobo : '0' }}">
        </div>
        <div class="checkbox">
            <label>
              <input type="checkbox" name="is_required" {{ isset($oCfg) && $oCfg->is_required ? 'checked' : '' }}> Es requerido
            </label>
        </div>
        <div class="checkbox">
            <label>
              <input type="checkbox" name="is_global" {{ isset($oCfg) && $oCfg->is_global ? 'checked' : '' }}> Es global
            </label>
        </div>
        <div class="form-group">
            <label for="rol_n_name">Rol de usuarios</label>
            <input type="text" class="form-control" id="rol_n_name" name="rol_n_name" placeholder="rol">
        </div>
        <div class="form-group">
            <label for="user_n_id">Usuario</label>
            <select class="form-control" id="user_n_id" name="user_n_id">
                @foreach ($users as $usr)
                    <option value="{{ $usr->id }}" {{ isset($oCfg) && $oCfg->user_n_id == $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>