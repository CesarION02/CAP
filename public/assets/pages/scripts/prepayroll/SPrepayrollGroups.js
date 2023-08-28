var app = new Vue({
    el: "#prepayrollApp",
    data: {
        lFathersGroups: oData.lFathersGroups,
        lTrees: [],
        oGroup: {},
        lUserCfgPrepayroll: [],
        oGui: new SGui()
    },
    mounted() {
        this.buildTrees();
    },
    methods: {
        /**
         * Arma el arreglo de nodos y mapea los padres e hijos para mostrar la estructura gráfica
         */
        buildTrees() {
            this.lTrees = [];
            for (const oFGroup of this.lFathersGroups) {
                let lTree = [];
                let lIds = [];
                let id = 1;
                for (const group of oFGroup.lGroups) {
                    lIds[group.id_group] = id;
                    let usersB = "";
                    if (group.head_users.length > 0) {
                        usersB = " " + group.head_users.map(obj => `(${obj['name']})`).join(", ");
                    }
                    let nodeT = {
                                    n_id: id,
                                    n_title: group.group_name.toUpperCase() + usersB,
                                    n_parentid: !!lIds[group.father_group_n_id] ? lIds[group.father_group_n_id] : 0,
                                    n_checked: true,
                                    l_users: group.head_users,
                                    n_elements: [
                                            /**
                                             * Botón para mostrar los usuarios encargados
                                             */
                                            {
                                                icon: "fa fa-user-check", // icon class
                                                title: "Encargados",
                                                title_text: "Muestra los usuarios encargados del grupo de prenómina",
                                                onClick: (node) => {
                                                    this.showUsers(node);
                                                },
                                            },
                                            /**
                                             * Botón para redirección a los empleados
                                             */
                                            {
                                                icon: "fa fa-users", // icon class
                                                title: "Empleados",
                                                title_text: "Muestra la vista de empleados pertenecientes al grupo de prenómina",
                                                onClick: () => {
                                                    this.showEmployees(group.emp_grp_route);
                                                },
                                            },
                                            /**
                                             * Botón para abrir la configuración de la prenómina
                                             */
                                            {
                                                icon: "fa fa-list-alt", // icon class
                                                title: "Prenómina",
                                                title_text: "Abre la configuración de prenómina correspondiente al usuario encargado",
                                                onClick: (node) => {
                                                    this.prepayroll(node, group.id_group);
                                                },
                                            },
                                            /**
                                             * Editar grupo de prenómina
                                             */
                                            {
                                                icon: "fa fa-edit", // icon class
                                                title: "Editar grupo",
                                                title_text: "En este apartado podemos editar el grupo de prenómina, su grupo padre y el usuario encargado",
                                                onClick: () => {
                                                    this.editGroup(group.edit_grp_route);
                                                },
                                            },
                                            /**
                                             * Eliminar grupo de prenómina
                                             */
                                            {
                                                icon: "fa fa-trash",
                                                title: "Borrar grupo",
                                                title_text: "Desactiva el grupo de prenómina, este puede recuperarse después en la vista de grupos de prenómina",
                                                onClick: () => {
                                                    this.deleteGroup(group.delete_grp_route);
                                                },
                                            },
                                    ]
                                };
                    lTree.push(nodeT)
                    id++;
                }

                let oTree = {
                                id_group: oFGroup.id_group,
                                treeL: lTree
                            };

                this.lTrees.push(oTree);
            }

            this.drawTree();
        },
        /**
         * Dibuja los diferentes árboles.
         * El método es asíncrono porque presentaba fallas al momento de dibujarse si se hacía en tiempo real
         */
        async drawTree() {
            for (const oTree of this.lTrees) {
                const tree = new PickleTree({
                    c_target: 'div_' + oTree.id_group,
                    c_config: {
                        // options here
                    },
                    c_data: oTree.treeL
                });
                await this.sleep(10);
            }
        },
        /**
         * Método de detención del proceso
         * 
         * @param int ms 
         * @returns 
         */
        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },
        /**
         * Método que setea el objecto actual para ser mostrado en el modal de usuarios
         * 
         * @param Object node 
         */
        showUsers(node) {
            this.oGroup = node.data;
            $("#usersModalId").modal();
        },
        /**
         * Redirecciona a la vista de empleados pertenecientes a un grupo de prenómina
         * 
         * @param string empGroupRoute 
         */
        showEmployees(empGroupRoute) {
            this.oGui.showLoading(3000);
            window.location.href = empGroupRoute;
        },
        /**
         * Redirecciona a la ruta de la edición de grupos de prenómina
         * 
         * @param string editGroupRoute 
         */
        editGroup(editGroupRoute) {
            this.oGui.showLoading(3000);
            window.location.href = editGroupRoute;
        },
        /**
         * Elimina el grupo de prenómina
         * 
         * @param string deleteGroupRoute 
         */
        deleteGroup(deleteGroupRoute) {
            (async () => {
                if (await this.oGui.confirm('','¿Desea borrar el grupo? Podrá recuperarlo en la vista de grupos de prenómina','warning')) {
                    
                    this.oGui.showLoading(3000);
                    let data = {
                        to_show : 1
                    };
                    axios.delete(deleteGroupRoute, { data })
                    .then(response => {
                        this.oGui.showOk();
                        location.reload();
                    })
                    .catch(error => {
                        this.oGui.showError("Error al eliminar el grupo de prenómina.");
                    });
                }
            })();
        },
        /**
         * Carga la información de la prenómina para ser mostrada y configurada
         * 
         * @param Object node 
         * @param int idGroup 
         */
        prepayroll(node, idGroup) {
            let oNodeData = node.data;
            this.lUserCfgPrepayroll = [];
            if (oNodeData.l_users.length > 0) {
                for (const usr of oNodeData.l_users) {
                    let oCfgUser = new SUserConfigAux(usr, idGroup);
                    this.lUserCfgPrepayroll.push(oCfgUser);
                    if (usr.cfg_prepayroll == null || usr.cfg_prepayroll.length == 0) {
                        this.oGui.showLoading(4000);
                        this.oCfgPrepayroll = app.readPrepayrollConfigurations(usr.id, idGroup, oCfgUser);
                    }
                }
            }
            // this.getPrepayrollConfigs(node.data);
            $("#pprModalId").modal();
        },
        /**
         * 
         * @param int idUser 
         * @param int idGroup 
         * @param SUserConfigAux oCfgUser 
         */
        readPrepayrollConfigurations(idUser, idGroup, oCfgUser) {
            let route = oData.getCfgsRoute + "/" + idUser + "/" + idGroup;

            axios.get(route)
                .then(response => {
                    // La respuesta del servidor se encuentra en 'response.data'
                    let lData = response.data;
                    this.setConfiguration(lData, oCfgUser);
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                });
        },
        /**
         * Setea la configuración encontrada en la BD
         * 
         * @param array lData 
         * @param SUserConfigAux oCfgUser 
         */
        setConfiguration(lData, oCfgUser) {
            for (const oCfg of lData) {
                if (oCfg.is_biweek) {
                    oCfgUser.oCfgPrepayroll.oBiweekCfg.idConfig = oCfg.id_configuration;
                    oCfgUser.oCfgPrepayroll.oBiweekCfg.isChecked = true;
                    oCfgUser.oCfgPrepayroll.oBiweekCfg.sinceDate = oCfg.since_date;
                    oCfgUser.oCfgPrepayroll.oBiweekCfg.untilDate = oCfg.until_date;
                    oCfgUser.oCfgPrepayroll.oBiweekCfg.isRequired = oCfg.is_required;
                    oCfgUser.oCfgPrepayroll.oBiweekCfg.isGlobal = oCfg.is_global;
                    oCfgUser.oCfgPrepayroll.oBiweekCfg.branch = oCfg.branch;
                }
                else {
                    oCfgUser.oCfgPrepayroll.oWeekCfg.idConfig = oCfg.id_configuration;
                    oCfgUser.oCfgPrepayroll.oWeekCfg.isChecked = true;
                    oCfgUser.oCfgPrepayroll.oWeekCfg.sinceDate = oCfg.since_date;
                    oCfgUser.oCfgPrepayroll.oWeekCfg.untilDate = oCfg.until_date;
                    oCfgUser.oCfgPrepayroll.oWeekCfg.isRequired = oCfg.is_required;
                    oCfgUser.oCfgPrepayroll.oWeekCfg.isGlobal = oCfg.is_global;
                    oCfgUser.oCfgPrepayroll.oWeekCfg.branch = oCfg.branch;
                }
            }
        },
        /**
         * Realiza la petición al servidor para el guardado de la configuración
         */
        saveConfig() {
            this.oGui.showLoading(5000);
            console.log(this.lUserCfgPrepayroll);
            let lCfgs = [];
            for (const oCfg of this.lUserCfgPrepayroll) {
                lCfgs.push(oCfg.oCfgPrepayroll);
            }
            let saveRoute = oData.saveCfgsRoute;
            let data = {
                l_user_cfg_prepayroll: JSON.stringify(this.lUserCfgPrepayroll)
              };
            // Realizar la petición POST utilizando Axios
            axios.post(saveRoute, data)
            .then(response => {
                this.oGui.showOk();
                location.reload();
            })
            .catch(error => {
                this.oGui.showError('Error en la petición:', error);
            });
        }
    },
});

/**
 * Clase de configuración de usuario
 */
class SUserConfigAux {
    oCfgPrepayroll = {};
    oUser = {};

    constructor(oUser, idGroup) {
        this.oUser = oUser;
        if (!! oUser.cfg_prepayroll) {
            this.oCfgPrepayroll = JSON.parse(oUser.cfg_prepayroll);
        }
        else {
            this.oCfgPrepayroll = new SPrepayrollConfig(oUser.id, idGroup);
        }
    }
}

/**
 * Clase auxiliar de configuración
 */
class SPrepayrollConfig {
    idUser = 0;
    idGroup = 0;
    oBiweekCfg = null;
    oWeekCfg = null;

    constructor(idUser, idGroup) {
        this.idUser = idUser;
        this.idGroup = idGroup;
        this.oBiweekCfg = new SCfg();
        this.oWeekCfg = new SCfg();
    }
}

/**
 * Clase de Modelo de configuración
 */
class SCfg {
    idConfig = 0;
    isChecked = false;
    sinceDate = null;
    untilDate = null;
    isRequired = false;
    isGlobal = false;
    branch = "";
}
