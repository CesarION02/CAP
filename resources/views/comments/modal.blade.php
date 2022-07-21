<!-- Modal -->
<div id="commentModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
  
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Nuevo comentario</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-3"><label for="">Comentario:</label></div>
                <div class="col-md-9">
                    <textarea v-model="comment" class="form-control">@{{comment}}</textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <div class="row">
            <div class="col-md-4" style="float: right;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" data-dismiss="modal" :disabled="modalDisabled" v-on:click="storeComment()">Guardar</button>
            </div>
          </div>
        </div>
      </div>
  
    </div>
  </div>