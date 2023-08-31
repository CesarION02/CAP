<!-- Modal -->
<div id="pprModalId" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
  
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Configuración de Vobo de prenómina</h4>  
        </div>
        <div class="modal-body">
            <div v-for="oUserCfg in lUserCfgPrepayroll">
                <div class="row">
                    <div class="col-md-offset-1 col-md-10" style="border: 1px solid #ccc!important; border-radius: 16px;">
                        <input type="text" class="form-control" :value="oUserCfg.oUser.name" disabled style="color: blue">
                        <div class="row" style="margin-top: 20px;">
                            {{-- Configuración de prenómina QUINCENA --}}
                            <div class="col-md-5" style="margin-left: 10px">
                                <div class="row">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="check_biweek" id="check_biweek" 
                                            v-model="oUserCfg.oCfgPrepayroll.oBiweekCfg.isChecked">
                                        Revisa quincena
                                        </label>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <label for="since_date">Desde:</label>
                                    <input type="date" name="since_date_biw" id="since_date_biw" class="form-control"
                                        v-model="oUserCfg.oCfgPrepayroll.oBiweekCfg.sinceDate" :disabled="! oUserCfg.oCfgPrepayroll.oBiweekCfg.isChecked">
                                </div>
                                <br>
                                <div class="row">
                                    <label for="since_date">Hasta:</label>
                                    <input type="date" name="since_date_biw" id="since_date_biw" class="form-control"
                                        v-model="oUserCfg.oCfgPrepayroll.oBiweekCfg.untilDate" :disabled="! oUserCfg.oCfgPrepayroll.oBiweekCfg.isChecked">
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                              <input type="checkbox" class="form-check-input" name="is_required_biw" id="is_required_biw" 
                                                v-model="oUserCfg.oCfgPrepayroll.oBiweekCfg.isRequired" :disabled="! oUserCfg.oCfgPrepayroll.oBiweekCfg.isChecked">
                                              Requerido
                                            </label>
                                          </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                              <input type="checkbox" class="form-check-input" name="is_global_biw" id="is_global_biw"
                                                v-model="oUserCfg.oCfgPrepayroll.oBiweekCfg.isGlobal" :disabled="! oUserCfg.oCfgPrepayroll.oBiweekCfg.isChecked">
                                              Es Vobo general
                                            </label>
                                          </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Configuración de prenómina SEMANA --}}
                            <div class="col-md-offset-1 col-md-5" style="margin-left: 40px">
                                <div class="row">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="check_week" id="check_week" 
                                            v-model="oUserCfg.oCfgPrepayroll.oWeekCfg.isChecked">
                                        Revisa semana
                                        </label>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <label for="since_date">Desde:</label>
                                    <input type="date" name="since_date_week" id="since_date_week" class="form-control"
                                        v-model="oUserCfg.oCfgPrepayroll.oWeekCfg.sinceDate" :disabled="! oUserCfg.oCfgPrepayroll.oWeekCfg.isChecked">
                                </div>
                                <br>
                                <div class="row">
                                    <label for="since_date">Hasta:</label>
                                    <input type="date" name="since_date_week" id="since_date_week" class="form-control"
                                        v-model="oUserCfg.oCfgPrepayroll.oWeekCfg.untilDate" :disabled="! oUserCfg.oCfgPrepayroll.oWeekCfg.isChecked">
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                              <input type="checkbox" class="form-check-input" name="is_required_week" id="is_required_week" 
                                                v-model="oUserCfg.oCfgPrepayroll.oWeekCfg.isRequired" :disabled="! oUserCfg.oCfgPrepayroll.oWeekCfg.isChecked">
                                              Requerido
                                            </label>
                                          </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                              <input type="checkbox" class="form-check-input" name="is_global_week" id="is_global_week"
                                                v-model="oUserCfg.oCfgPrepayroll.oWeekCfg.isGlobal" :disabled="! oUserCfg.oCfgPrepayroll.oWeekCfg.isChecked">
                                              Es Vobo general
                                            </label>
                                          </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" v-on:click="saveConfig()">Guardar</button>
          <button type="button" class="btn btn-warning" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
  
    </div>
  </div>