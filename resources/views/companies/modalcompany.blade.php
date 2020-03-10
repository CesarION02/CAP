<!-- Modal -->
<div id="modalCompany" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Empresa</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-3">RFC</div>
          <div class="col-md-9">
            <input type="text" class="form-control" v-model="oCompany.fiscal_id">
          </div>
        </div>
        <br>
        <div class="row">
          <div class="col-md-3">Nombre</div>
          <div class="col-md-9">
            <input type="text" class="form-control" v-model="oCompany.name">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" v-on:click="processCompany()">Guardar</button>
      </div>
    </div>

  </div>
</div>