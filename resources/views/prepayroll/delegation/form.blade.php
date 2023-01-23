<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="form-group">
            <label for="">Tipo de pago</label>
            <select name="pay_way_id" class="form-control" v-model="payWayId">
                <option value="2" selected>Semana</option>
                <option value="1">Quincena</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="form-group">
            <label for="">Num prenómina</label>
            <select v-if="payWayId == 1" class="form-control" name="number_prepayroll" required>
                <option v-for="qCut in oDates.biweeks" :value="qCut.number + '_' + qCut.year">
                    @{{ "Qna " + qCut.number + " [" + qCut.dt_start + " - " + qCut.dt_end + "]" }}
                </option>
            </select>
            <select v-else class="form-control" name="number_prepayroll" required>
                <option v-for="wCut in oDates.weeks" :value="wCut.number + '_' + wCut.year">
                    @{{ "Sem " + wCut.number + " [" + wCut.dt_start + " - " + wCut.dt_end + "]" }}
                </option>
            </select>
        </div>
    </div>
</div>
<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="form-group">
      <label for="">Usuario ausente</label>
      <select name="user_delegation_id" class="select2-class" style="width: 100%">
        @foreach ($lDelegationUsers as $usr)
          <option value="{{ $usr->id }}">{{ $usr->name }}</option>
        @endforeach
      </select>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="form-group">
      <label for="">Usuario encargado</label>
      <select name="user_delegated_id" class="select2-class" style="width: 100%">
        @foreach ($lToDelegationUsers as $usr)
          <option value="{{ $usr->id }}">{{ $usr->name }}</option>
        @endforeach
      </select>
    </div>
  </div>
</div>
