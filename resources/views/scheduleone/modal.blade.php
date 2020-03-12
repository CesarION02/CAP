<!-- Modal -->
<div id="modalScheduleOne" class="modal fade" role="dialog">
    <div class="modal-dialog">
    
        <!-- Modal content-->
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Guardia</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-2 col-md-offset-1">
                    <label for="">Fecha</label>
                </div>
                <div class="col-md-5">
                    <input id="st_date" type="date" v-model="dtDate" class="form-control">
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-2 col-md-offset-1">
                    <label for="">Empleado</label>
                </div>
                <div class="col-md-8">
                    <select v-model="iEmployee"
                            data-placeholder="Selecciona empleado..." id="sel_emp" 
                            style="width: 100%"
                            class="chosen-select"
                            >
                        <option v-for="employee in vueServerData.lEmployees"
                                :value="employee.id">@{{ employee.name + ' - ' + employee.num_employee }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-success" v-on:click="processAssignament()">Guardar</button>
        </div>
        </div>
    
    </div>
</div>