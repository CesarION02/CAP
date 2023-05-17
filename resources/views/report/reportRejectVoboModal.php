<!-- Modal -->
<div class="modal fade" id="rejectModalId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
                <div class="modal-header">
                        <h5 class="modal-title">Rechazo de Vo.Bo.</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col">
                            <label for="rejectReason">Motivo de rechazo</label>
                            <textarea class="form-control" name="rejectReason" id="rejectReason" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="idRejectButton" class="btn btn-danger">Rechazar Vo.Bo.</button>
            </div>
        </div>
    </div>
</div>