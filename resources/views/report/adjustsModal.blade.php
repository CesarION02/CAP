<!-- Modal -->
<div id="adjustsModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Ajustes de prenómina</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-offset-1 col-xs-11">
            <div class="form-check">
              <input class="form-check-input" type="radio" v-model="adjCategory" v-on:change="onAdjustChange()" value="2">
              <label class="form-check-label">
                Crear comentario
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" v-model="adjCategory" v-on:change="onAdjustChange()" value="0">
              <label class="form-check-label">
                Ajustar prenómina
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" v-model="adjCategory" v-on:change="onAdjustChange()" value="1">
              <label class="form-check-label">
                Ajustar entrada y/o salida
              </label>
            </div>
          </div>
        </div>
        <br>
        <br>
        <div v-if="adjCategory == 0 || adjCategory == 2" class="row">
          <div class="row">
            <div class="col-md-offset-1 col-md-10">
              <div class="row">
                <div class="col-md-4"><label for="">Tipo de ajuste:*</label></div>
                <div class="col-md-8">
                  <select :disabled="!adjTypeEnabled" v-model="adjType" class="form-control" v-on:change="onTypeChange()">
                    <option v-for="adjT in vData.adjTypes" :value="adjT.id">@{{ adjT.type_code + '-' + adjT.type_name }}</option>
                  </select>
                </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-4"><label for="">Minutos:</label></div>
                <div class="col-md-8">
                  <input v-model="overMins" :disabled="! minsEnabled" type="number" min="0" class="form-control">
                </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-4"><label for="">Comentarios frecuentes:</label></div>
                <div class="col-md-7">
                  <select class="form-control" v-model="selComment" :disabled="!haveComments">
                    <option v-for="comment in lComments">@{{comment.comment}}</option>
                  </select>
                </div>
                <div class="col-md-1">
                  <button class="btn btn-success" style="border-radius: 50%; padding: 3px 6px; font-size: 10px;" v-on:click="addComment()" :disabled="!haveComments"><span class="glyphicon glyphicon-plus"></span></button>
                </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-4"><label for="">Comentarios:</label></div>
                <div class="col-md-8">
                  <textarea v-model="comments" class="form-control" style="resize: none; width: 350px; height: 115px;">@{{comments}}</textarea>
                </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-offset-10 col-md-1">
                  <button type="button" v-on:click="newAdjust()" class="btn btn-success">Ajustar</button>
                </div>
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-offset-1 col-md-10">
              <ul class="list-group">
                <li v-for="rowAdj in rowAdjusts" class="list-group-item list-group-item-info">
                      @{{ rowAdj.type_code + '-' + rowAdj.type_name + 
                        ((rowAdj.comments != null && rowAdj.comments.length > 0) ? (' / ' + rowAdj.comments) : '')
                        + (rowAdj.minutes > 0 ? (' / ' + rowAdj.minutes + ' min') : '') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" v-on:click="deleteAdjust(rowAdj)">
                        <span aria-hidden="true">&times;</span>
                      </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div v-else class="row">
          <div class="row">
            <div class="col-xs-8 col-xs-offset-1">
              <div class="form-group">
                <label for="inDateTime">Entrada</label>&nbsp;<input type="checkbox" v-model="isModifIn">(Modificar)
                <input :readonly="! isModifIn" type="datetime-local" class="form-control" id="inDateTime" v-model="inDateTime" placeholder="2021-10-01 13:03">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-8 col-xs-offset-1">
              <div class="form-group">
                <label for="outDateTime">Salida</label>&nbsp;<input type="checkbox" v-model="isModifOut">(Modificar)
                <input :readonly="! isModifOut" type="datetime-local" class="form-control" id="outDateTime" v-model="outDateTime" placeholder="2021-10-01 13:03">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xs-3 col-xs-offset-9">
              <button :disabled="!isModifOut && !isModifIn" type="button" class="btn btn-success" v-on:click="adjustTimes()">Ajustar</button>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div class="row">
          <div class="col-md-8" style="color:red">
            NOTA: Los cambios se verán reflejados hasta que el reporte sea generado de nuevo.
          </div>
          <div class="col-md-4">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>