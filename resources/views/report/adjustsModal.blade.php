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
          <div class="col-md-11 col-md-offset-1">
            <small class="text-muted">Pasa el puntero por encima de los controles para mostrar ayuda y sugerencias</small>
            <br>
            <br>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-offset-1 col-xs-11">
            <div class="form-check">
              <input :disabled="checkEmployee" class="form-check-input" type="radio" v-model="adjCategory" v-on:change="onAdjustChange()" value="2">
              <label title="Esta opción solo agrega un texto al renglón, no genera ninguna acción adicional."  class="form-check-label">
                Crear comentario
              </label>
            </div>
            <div class="form-check">
              <input :disabled="checkEmployee" class="form-check-input" type="radio" v-model="adjCategory" v-on:change="onAdjustChange()" value="0">
              <label title="En esta opción puedes crear ajustes de prenómina (necesario recargar el reporte para que se vean reflejados)."  class="form-check-label">
                Ajustar prenómina
              </label>
            </div>
            <div class="form-check">
              <input :disabled="checkEmployee" class="form-check-input" type="radio" v-model="adjCategory" v-on:change="onAdjustChange()" value="3">
              <label title="Crear un ajuste y/o comentario que aplique a varios renglones a la vez." class="form-check-label">
                Ajustar/comentar varios renglones
              </label>
            </div>
            <div class="form-check">
              <input :disabled="checkEmployee" class="form-check-input" type="radio" v-model="adjCategory" v-on:change="onAdjustChange()" value="1">
              <label title="Modificar entrada y/o salida del renglón." class="form-check-label">
                Ajustar entrada y/o salida
              </label>
            </div>
          </div>
        </div>
        <br>
        <br>
        <div v-if="adjCategory == 0 || adjCategory == 2 || adjCategory == 3" class="row">
          <div class="row">
            <div class="col-md-offset-1 col-md-10">
              <div class="row">
                <div class="col-md-4"><label for="">Tipo de ajuste:*</label></div>
                <div class="col-md-8">
                  <select :disabled="!adjTypeEnabled || checkEmployee" v-model="adjType" class="form-control" v-on:change="onTypeChange()">
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
              <div v-if="adjCategory == 3" class="row">
                <div class="col-md-4">
                  <label>Rango:</label>
                </div>
                <div class="col-md-4">
                  <label for="initDate">Fecha inicio:</label>
                  <input type="date" name="initDate" v-model="dateInit" value="" :min="startDate" :max="endDate"/>
                </div>
                <div class="col-md-4">
                  <label for="endDate">Fecha fin:</label>
                  <input type="date" name="endDate" v-model="dateEnd" value="" :min="startDate" :max="endDate"/>
                </div>
              </div>
              <br>
              <div v-if="adjCategory != 3" class="row">
                <div class="col-md-4"><label for="">Copiar comentario anterior:</label></div>
                <div class="col-md-1">
                  <button :disabled="checkEmployee" class="btn btn-success" 
                          title="Este botón copia únicamente el comentario del renglón anterior" 
                          v-on:click="addPreviusComment()"><span class="glyphicon glyphicon-copy"></span> Copiar anterior</button>
                </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-4"><label for="">Comentarios frecuentes:</label></div>
                <div class="col-md-8">
                  <select class="form-control select2-class" id="comentFrec" style="width: 90%;" 
                          title="Lista de comentarios frecuentes." 
                          v-model="selComment" :disabled="!haveComments || checkEmployee">
                    <option v-for="comment in lComments">@{{comment.comment}}</option>
                  </select>
                  <button :disabled="checkEmployee" class="btn btn-success" 
                          title="Agregar texto."
                          style="border-radius: 50%; padding: 3px 6px; font-size: 10px;" 
                          v-on:click="addComment()" :disabled="!haveComments"><span class="glyphicon glyphicon-arrow-right"></span></button>
                  <small class="text-muted">Debe dar click en el botón <span class="glyphicon glyphicon-arrow-right"></span> para agregar comentario</small>
                </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-4"><label for="">Comentarios:</label></div>
                <div class="col-md-8">
                  <textarea :disabled="checkEmployee" 
                            title="Escribe el comentario que deseas que aparezca en el renglón."
                            v-model="comments" class="form-control" 
                            style="resize: none; width: 350px; height: 115px;">@{{comments}}</textarea>
                </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-offset-10 col-md-1">
                  <button :disabled="checkEmployee" 
                          title="Guardar ajuste/comentario"
                          type="button" v-on:click="newAdjust()" 
                          class="btn btn-success">Ajustar</button>
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
                    <button v-if="!checkEmployee" type="button" class="close" data-dismiss="alert" aria-label="Close" v-on:click="deleteAdjust(rowAdj)">
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
          <div v-if="checkEmployee" class="row">
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
            <button type="button" class="btn btn-default" title="Cancelar" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>