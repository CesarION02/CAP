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
      onFilterTypeChange() {
        this.iPayWay = 2;
      }
    },
  })