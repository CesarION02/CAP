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
                    <label for="">Fecha:*</label>
                </div>
                <div class="col-md-4">
                    <input id="st_date" type="date" v-model="dtDate" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="">Es festivo:</label>
                </div>
                <div class="col-md-1">
                    <input id="is_holiday" type="checkbox" v-model="bHoliday">
                </div>
            </div>
            <br>
            <div v-show="! bHoliday" class="row">
                <div class="col-md-2 col-md-offset-1">
                    <label for="">Empleado:</label>
                </div>
                <div class="col-md-8">
                    <div>
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
            <div v-show="bHoliday" class="row">
                <div class="col-md-2 col-md-offset-1">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="">Festivo:</label>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="">Observ.:</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <select v-model="iHoliday"
                                data-placeholder="Selecciona festivo..." id="sel_hol" 
                                style="width: 100%"
                                class="chosen-select"
                                >
                            <option v-for="holiday in vueServerData.holidays"
                                :value="holiday.id">
                                    @{{ (holiday.fecha == undefined ? '-' : holiday.fecha) + ' - ' + (holiday.name == undefined ? '-' : holiday.name) }}
                                </option>
                        </select>
                    </div>
                    <br>
                    <div class="row">
                        <input type="text" class="form-control input-sm" v-model="sDescription">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-success" v-on:click="processAssignament()">Guardar</button>
        </div>
        </div>
    
    </div>
</div>