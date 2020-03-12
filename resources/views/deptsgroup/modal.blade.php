<div id="groupModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
    
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@{{ "Grupo " + oGroup.name }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-9 col-md-offset-1">
                        <select class="form-control" name="" id="" v-model="iDept">
                            <option v-if="dept.dept_group_id == null" v-for="dept in lDepartments" :value="dept.id">@{{ dept.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button v-on:click="addDepartment()" style="float: right;" class="btn btn-success btn-xs">
                            <i class="glyphicon glyphicon-plus"></i>
                        </button>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <ul class="list-group">
                            <li v-for="dept in lDepartments" class="list-group-item" v-if="dept.dept_group_id == oGroup.id">
                                <span>
                                    <button v-on:click="removeDepartment(dept)" style="float: right;" class="btn btn-danger btn-xs">
                                        <i class="glyphicon glyphicon-remove"></i>
                                    </button>
                                </span>
                                @{{ dept.name }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" v-on:click="saveDepartments()">Guardar</button>
            </div>
        </div>
    
    </div>
</div>