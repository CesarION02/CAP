var app = new Vue({
    el: '#deptsGrpApp',
    data: {
      vueData: oServerData,
      lDepartments: [],
      lFreeDepartments: [],
      oGroup: { name : '' },
      iDept: null,
      iAction: 1,
      iFilter: 1 // 1:activos, 2: inactivos, 3: todos
    },
    methods: {

        /**
         * Inicializa un registro nuevo y
         * Muestra el modal para recibir la información
         */
        newGroupModal() {
            this.iAction = 1;
            this.oGroup = { name : '',
                            id: 0,
                            is_delete: false };

            $("#editM").modal();
        },

        /**
         * este métopdo es llamado por el modal de creación/edición
         * determina cuál es la acción que continua, creación o edición
         */
        processGroup() {
            switch (this.iAction) {
                case 1:
                    this.saveGroup();
                    break;
                case 2:
                    this.editGroup();
                    break;
            
                default:
                    break;
            }
        },

        /**
         * Realiza una llamada al servidor para insertar el nuevo registro en 
         * la base datos
         */
        saveGroup() {
            oGui.showLoading(3000);
            let route = './deptsgroup';
   
            axios.post(route, {
                group: JSON.stringify(this.oGroup)
            })
            .then(res => {
                console.log(res);

                oGui.showOk();
                location.reload();
            })
            .catch(function(error) {
                console.log(error);
            });
        },

        /**
         * Muestra el modal para la edición de los departamentos en los grupos
         * Inicializa un arreglo auxiliar de departamentos y trabaja con ellos,
         * por si en caso de que el usuario decida no continuar no se hagan modificaciones
         * 
         * @param {*} objGroup 
         */
        showGrpModal(objGroup) {
            this.oGroup = objGroup;
            this.createLDepartments();
            $("#groupModal").modal();
        },

        /**
         * crea una copia de los departamentos
         */
        createLDepartments() {
            this.lDepartments = [];
            
            for (const dept of this.vueData.lDepts) {
                this.lDepartments.push(this.copyDepartment(dept));
            }
        },

        /**
         * Cuando un departamento es añadido la llave foránea se actualiza 
         * para que quede ligado al grupo, por lo tanto desaparece del select y aparece ahora en los
         * departamentos asigvnados al grupo
         */
        addDepartment() {
            for (const dept of this.lDepartments) {
                if (dept.id == this.iDept) {
                    if(dept.dept_group_id != null){
                        (async () => {
                            if (await oGui.confirm(dept.name + ' está asignado.','Desea actualizar la asignación del departamento?','warning')) {
                                dept.dept_group_id = this.oGroup.id;
                            }
                        })();
                        break;
                    }else{
                        dept.dept_group_id = this.oGroup.id;
                        break;
                    }
                }
            }
        },

        /**
         * al departamento se le asigna un nulo en la llave foránea, con esto se detecta
         * que ya no corresponde a ningún grupo
         * 
         * @param {SDepartment} dept 
         */
        removeDepartment(dept) {
            dept.dept_group_id = null;
        },

        /**
         * Envía al servidor una lista de departamentos actualizados
         * para que sean modificados en la base datos, departamentos vinculados 
         * y desvinculados.
         */
        saveDepartments() {
            oGui.showLoading(3000);
            let route = './upddepartments';

            axios.put(route, {
                departments: JSON.stringify(this.lDepartments)
            })
            .then(res => {
                this.vueData.lDepts = res.data;
                this.setDepartments();

                $("#groupModal").modal("hide");

                oGui.showOk();
            })
            .catch(function(error) {
                console.log(error);
            });
        },

        /**
         * Actualiza el campo de departamentos asignados en base a los
         * datos recibidos del servidor, esto actualiza en automático la 
         * vista
         */
        setDepartments() {
            for (const grp of this.vueData.lGroups) {
                grp.depts = "";

                for (const dept of this.vueData.lDepts) {
                    if (dept.dept_group_id == grp.id) {
                        grp.depts += dept.name + ", ";
                    }
                }
            }
        },

        /**
         * 
         * @param {SDepartment} oDept 
         */
        copyDepartment(oDept) {
            let dep = new SDepartment();
            dep.id = oDept.id;
            dep.name = oDept.name;
            dep.dept_group_id = oDept.dept_group_id;

            return dep;
        },

        /**
         * Muestra el modal para que un registro sea editado
         * 
         * @param {*} oGrp 
         */
        editGrpModal(oGrp) {
            this.iAction = 2;
            this.oGroup = oGrp;
            $("#editM").modal();
        },

        /**
         * realiza la petición al servidor para la modificación del 
         * registro
         */
        editGroup() {
            oGui.showLoading(3000);

            let route = './deptsgroup/' + this.oGroup.id + "/" + this.oGroup.name;

            axios.put(route)
            .then(res => {
                console.log(res);

                $("#editM").modal("hide");

                oGui.showOk();
            })
            .catch(function(error) {
                console.log(error);
            });
        },
        /**
         * Pide al usuario una confirmación para poder eliminar el registro
         * 
         * @param {DepartmentsGroup} oGrp 
         */
        prevDeleteGroup(oGrp) {
            

            if (oGrp.is_delete) {
                oGui.showLoading(3000);

                this.deleteGrpModal(oGrp);
            }
            else {
                if (oGrp.depts.length > 0) {
                    oGui.showError('El grupo tiene departamentos asignados');
                    return;
                }

                swal({
                    title: "¿Seguro?",
                    text: "Está por eliminar la el grupo " + oGrp.name,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                  })
                  .then((willDelete) => {
                    if (willDelete) {
                        oGui.showLoading(3000);
    
                        this.deleteGrpModal(oGrp);
                    }
                });
            }
        },
        
        /**
         * Realiza la petición al servidor para eliminar el grupo
         * 
         * @param {DepartmentsGroup} oGrp 
         */
        deleteGrpModal(oGrp) {
            let route = './deptsgroup/' + oGrp.id;

            axios
            .delete(route)
            .then(res => {
                oGui.showOk();

                location.reload();
            })
            .catch(function(error) {
                console.log(error);
            });
        }
    },
    created() {
        this.setDepartments();
    },
  })