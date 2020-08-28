var app = new Vue({
    el: '#reportApp',
    data: {
      oData: oData,
      vueGui: oGui
    },
    methods: {
        onChangeOp() {
            $(".chosen-select").trigger("chosen:updated");
        }
    },
  })