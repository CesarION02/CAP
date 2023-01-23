<div class="row">
    <div class="col-md-2">
        Prenóminas delegadas:
    </div>
    <div class="col-md-6 col-md-offset-1">
        <div class="form-group">
          <label for=""></label>
          <select v-if="iPayWay == 2" v-model="oDelegation" class="form-control" v-on:change="onCutoffDateChange()" placeholder="Seleccione prenómina" required>
            <option v-for="sCut in oPayrolls.weeks" 
                    :value="sCut" 
                    v-on:change="onCutoffDateChange()">
               [Sem. @{{ sCut.number }}] / @{{ sCut.start_date }} - @{{ sCut.end_date }} / Por: @{{ sCut.user_delegation }}
            </option>
          </select>
          <select v-else v-model="oDelegation" class="form-control" v-on:change="onCutoffDateChange()" placeholder="Seleccione prenómina" required>
            <option v-for="qCut in oPayrolls.biweeks" 
                    :value="qCut" 
                    v-on:change="onCutoffDateChange()">
               [Qna. @{{ qCut.number }}] / @{{ qCut.start_date }} - @{{ qCut.end_date }} / Por: @{{ qCut.user_delegation }}
            </option>
          </select>
        </div>
    </div>
    <input v-model="startDate" type="hidden" name="start_date">
    <input v-model="endDate" type="hidden" name="end_date">
    <input v-model="payrollNumber" type="hidden" name="payroll_number">
    <input v-model="payrollYear" type="hidden" name="year">
    <input v-model="idDelegation" type="hidden" name="id_delegation">
</div>
<br>