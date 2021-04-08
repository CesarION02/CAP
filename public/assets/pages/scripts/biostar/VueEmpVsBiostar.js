var app = new Vue({
    el: '#appEmpVsBiostar',
    data: {
      lVueEmployees: [],
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
        oGui.showLoading(3000);

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