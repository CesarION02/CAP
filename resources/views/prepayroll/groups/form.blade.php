<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="form-group">
          <label for="">Nombre de grupo de prenómina</label>
          <input type="text" value="{{ isset($oPpGroup) ? $oPpGroup->group_name : '' }}" name="group_name" class="form-control" placeholder="Grupo de prenómina" required>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="form-group">
          <label for="">Grupo de prenómina padre</label>
          <select class="form-control" name="father_group_n_id">
            @foreach ($lGroups as $group)
                <option value="{{ $group->id_group }}" {{ (isset($oPpGroup) && $oPpGroup->father_group_n_id == $group->id_group) || (!isset($oPpGroup) && $group->id_group == null) ? 'selected' : '' }} required>
                    {{ $group->group_name }}
                </option>
            @endforeach
          </select>
        </div>
    </div>
</div>
<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="form-group">
      <label for="">Usuarios encargados</label>
      <select class="head-users" name="head_users[]" multiple="multiple" style="width: 100%;">
        @foreach ($lHeadUsers as $usr)
            @if (isset($oPpGroup))
              <option value="{{ $usr->id }}" {{ in_array($usr->id, $lHeadUsersSelected) ? 'selected' : '' }}>{{ $usr->usr_name }}</option>
            @else
              <option value="{{ $usr->id }}">{{ $usr->usr_name }}</option>
            @endif
        @endforeach
      </select>
    </div>
  </div>
</div>
