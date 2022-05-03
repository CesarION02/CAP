var app = new Vue({
    el: '#faltasReport',
    data: {
        Employee: null,
        lfaltas: null
    },
    methods: {
        setEmpl(empleado, jfaltas){
            this.Employee = empleado;
            this.lfaltas = jfaltas;
        }
    },
})