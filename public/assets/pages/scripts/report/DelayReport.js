var app = new Vue({
    el: '#reportDelayApp',
    data: {
      oData: oData,
      vueGui: oGui
    },
    methods: {
      getCssClass(mins, report) {
        if (mins > 0) {
          if (report == this.oData.REP_DELAY) {
            return 'danger';
          }
        }
      }
    },
  })