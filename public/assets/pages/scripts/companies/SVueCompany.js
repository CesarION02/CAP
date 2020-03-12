var app = new Vue({
    el: '#companiesApp',
    data: {
      vueData: oServerData,
      oCompany: new SCompany(),
      iAction: 1 // 1: crear, 2: editar
    },
    methods: {
        editCompany(comp) {
            this.oCompany = comp;
            this.iAction = 2;

            $('#modalCompany').modal();
        },
        createCompany() {
            this.oCompany = new SCompany();
            this.iAction = 1;

            $('#modalCompany').modal();
        },
        processCompany() {
            oGui.showLoading(3000);

            switch (this.iAction) {
                case 1:
                    this.saveCompany();
                    break;

                case 2:
                    this.updateCompany();
                    break;
            
                default:
                    break;
            }
        },
        saveCompany() {
            let route = "./company";

            axios.post(route, {
                company: JSON.stringify(this.oCompany)
            })
            .then(res => {
                console.log(res);

                $("#modalCompany").modal("hide");
                oGui.showOk();
                location.reload();
            })
            .catch(function(error) {
                console.log(error);
            });
        },
        updateCompany() {
            let route = "./company/" + this.oCompany.id;

            axios.put(route, {
                company: JSON.stringify(this.oCompany)
            })
            .then(res => {
                console.log(res);

                $("#modalCompany").modal("hide");
                oGui.showOk();
            })
            .catch(function(error) {
                console.log(error);
            });
        },
        prevDeleteCompany(company) {
            if (company.is_delete) {
                oGui.showLoading(3000);

                this.deleteCompany(company.id);
                return;
            }

            swal({
                title: "¿Seguro?",
                text: "Está por eliminar la empresa " + company.name,
                icon: "warning",
                buttons: true,
                dangerMode: true,
              })
              .then((willDelete) => {
                if (willDelete) {
                    oGui.showLoading(3000);

                    this.deleteCompany(company.id);
                }
            });
        },
        deleteCompany(id) {
            let route = './company/' + id;

            axios
            .delete(route)
            .then(res => {
                let obj = res.data;

                oGui.showOk();
                location.reload();
            })
            .catch(function(error) {
                console.log(error);
            });
        }
    },
  })