<!-- Modal -->
<div id="adjustsModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Ajustes de Prenómina</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-offset-1 col-md-10">
            <div class="row">
              <div class="col-md-4"><label for="">Tipo de Ajuste</label></div>
              <div class="col-md-8">
                <select v-model="adjType" class="form-control" v-on:change="onTypeChange()">
                  <option v-for="adjT in vData.adjTypes" :value="adjT.id">@{{ adjT.type_code + '-' + adjT.type_name }}</option>
                </select>
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-md-4"><label for="">Minutos</label></div>
              <div class="col-md-8">
                <input v-model="overMins" :disabled="! minsEnabled" type="number" min="0" class="form-control">
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-md-4"><label for="">Comentarios</label></div>
              <div class="col-md-8">
                <input v-model="comments" type="text" class="form-control">
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
              <li v-for="rowAdj in rowAdjusts" class="list-group-item list-group-item-info">@{{ rowAdj.type_code + 
                                                                    '-' + rowAdj.type_name + (rowAdj.comments.length > 0 ? (' / ' + rowAdj.comments) : '') }}
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close" v-on:click="deleteAdjust(rowAdj)">
                      <span aria-hidden="true">&times;</span>
                    </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>