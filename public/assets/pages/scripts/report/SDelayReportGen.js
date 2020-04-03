var app = new Vue({
    el: '#reportDelayAppGen',
    data: {
      startDate: '',
      endDate: ''
    },
    methods: {
      setDates(sd, ed) {
        this.startDate = sd;
        this.endDate = ed;
      }
    },
  })