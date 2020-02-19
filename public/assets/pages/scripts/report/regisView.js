var app = new Vue({
    el: '#reportApp',
    data: {
      oData: oData
    },
    methods: {
        onChangeOp() {
            $(".chosen-select").trigger("chosen:updated");
        }
    },
  })