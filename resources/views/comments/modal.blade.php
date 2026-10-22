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
                <!-- Comentario -->
                <div class="row">
                    <div class="col-md-3"><label for="">Comentario:</label></div>
                    <div class="col-md-9">
                        <textarea v-model="comment" class="form-control">@{{comment}}</textarea>
                    </div>
                </div>

                <!-- Tipos de ajuste -->
                <div class="row" style="margin-top: 15px;">
                    <div class="col-md-3"><label for="">Tipos de ajuste:</label></div>
                    <div class="col-md-9">
                        <select multiple class="js-example-basic-multiple" style="width: 100%;" v-model="selectedAdjustTypes">
                            <option v-for="type in lAdjustTypes" :value="type.id">@{{ type.type_name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" :disabled="modalDisabled" v-on:click="storeComment()">Guardar</button>
          </div>
        </div>

    </div>
</div>