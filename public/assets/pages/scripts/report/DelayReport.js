var app = new Vue({
    el: '#reportDelayApp',
    data: {
      oData: oData,
      vueGui: oGui
    },
    methods: {
      getCssClass(oRow, report) {
        if (oRow.delayMins > 0) {
          if (report == this.oData.REP_DELAY) {
            return 'danger';
          }
        }
        if (oRow.isCheckSchedule) {
          return 'check';
        }
      }
    },
  })