var app = new Vue({
    el: '#reportApp',
    data: {
      oData: oData,
      startDate: (new Date()).toISOString().split('T')[0],
      endDate: (new Date()).toISOString().split('T')[0]
    },
    methods: {
        onChangeOp() {
            $(".chosen-select").trigger("chosen:updated");
        }
    },
  })