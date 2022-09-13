<!-- Modal -->
<div id="copyModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Copiar el usuario: @{{userName}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <label for="copyUser" style="width: 99%;">Seleccione usuario de destino:</label>
                    <select class="js-example-basic-multiple" name="copyUser" id="copyUser" style="width: 70%">
                        <option value=""></option>
                        @foreach($datas as $data)
                            <option value="{{$data->id}}">{{$data->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="row">
            <div class="col-md-4" style="float: right;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" v-on:click="storeCopyUser()">Copiar</button>
            </div>
            </div>
        </div>
        </div>

    </div>
</div>