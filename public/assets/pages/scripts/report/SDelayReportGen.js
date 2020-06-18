var app = new Vue({
    el: '#reportDelayAppGen',
    data: {
      lEmps: oData.lEmployees,
      iPayWay: 2,
      startDate: '',
      endDate: '',
      picked: 'period',
      idEmployee: null
    },
    methods: {
      setDates(sd, ed) {
        this.startDate = sd;
        this.endDate = ed;
      },
      filterEmployees() {
        if (this.iPayWay > 0) {
          let emps = [];
          for (const emp of oData.lEmployees) {
            if (emp.way_pay_id == this.iPayWay) {
              emps.push(emp);
            }
          }

          this.lEmps = emps;
        }
        else {
          this.lEmps = oData.lEmployees;
        }
      }
    },
  })