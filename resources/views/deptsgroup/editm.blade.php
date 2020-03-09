<!-- Modal -->
<div id="editM" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">
  
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Grupo</h4>
        </div>
        <div class="modal-body">
          <div class="row">
              <div class="col-md-12">
                <label for="">Nombre</label>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <input type="text" v-model="oGroup.name" class="form-control">
              </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-info" v-on:click="processGroup()">Guardar</button>
        </div>
      </div>
  
    </div>
  </div>