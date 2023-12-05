var app = new Vue({
    el: '#appEmpVsBiostar',
    data: {
      lVueEmployees: [],
      lUser: oData.lUsers,
      oVueGui: oGui
    },
    methods: {
      editBiostarId(oEmpRow) {
        oEmpRow.actionEnabled = !oEmpRow.actionEnabled;
        if (oEmpRow.actionEnabled) {
          document.getElementById(oEmpRow.id).focus();
          document.getElementById(oEmpRow.id).select();
        }
      },
      updateBiostarId(oEmpRow) {
        var nombre = "";
        for(var i = 0 ; oData.lUsers.length > i ; i++){
          if(oData.lUsers[i].user_id == oEmpRow.biostar_id){
            nombre = oData.lUsers[i].name;
            break;
          }
        }
        if( nombre != ''){
          swal({
              title: 'Empleado a enlazar',
              text: 'Se enlazará este registro con '+nombre,
              icon: "warning",
              buttons: true,
              dangerMode: false,
          })
          .then((value) => {
              if (value) {
                let route = './updatebiostarid';
                axios.put(route, {
                    emp_row: JSON.stringify(oEmpRow),
                })
                .then(res => {
                    console.log(res);
                    oEmpRow.actionEnabled = false;
                    oGui.showOk();
                })
                .catch(function(error) {
                    console.log(error);
                });
              } else {
                  return false;
              }
          });
        }
        
        //oGui.showLoading(3000);

        

      }
    },
    mounted: function () {
      // `this` points to the vm instance
      let rows = [];
      for (const emp of oData.lEmployees) {
        emp.editEnabled = true;
        emp.actionEnabled = false;

        rows.push(emp);
      }
      
      this.lVueEmployees = rows;
    }
})