var app = new Vue({
    el: '#divRegistries',
    data: {
        message: 'Hello Vue!',
        isSingle: true,
        picked: 'single'
    },
    methods: {
        onTypeChange() {
            this.isSingle = this.picked == 'single';
        }
    },
  })