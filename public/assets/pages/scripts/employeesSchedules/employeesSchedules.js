var app = new Vue({
    el: '#employeesSchedulesApp',
    data: {
        lEmployees: oData.lEmployees,
        selEmployee: null,
        lSchedules: oData.lSchedules,
        oEmployee: oData.oEmployee,
        routeDelSchedules: oData.routeDelSchedules,
        routeProgramming: oData.routeProgramming,
        lScheduleDays: null,
    },
    mounted(){
        let self = this;
        var opciones = [];

        if(this.oEmployee.employee_id != 0){
            this.selEmployee = this.oEmployee.employee_id;
        }

        for (let i = 0; i < self.lEmployees.length; i++) {
            opciones.push({id: self.lEmployees[i].employee_id, text: self.lEmployees[i].employee});
        }

        $('#select_employees').select2({
            placeholder: "selecciona empleado",
            data: opciones,
        }).on('select2:select', function (e) {
            self.selEmployee = e.params.data.id;
            // self.oEmployee = self.lEmployees.find( ({ employee_id }) => employee_id == e.params.data.id );
        });
    },
    methods: {

        deleteSchedule(id, start_date, end_date){
            var route = this.routeDelSchedules.replace(':id', id);
            (async () => {
                if (await oGui.confirm('', 'Eliminar horario del ' + start_date + ' al ' + end_date, 'warning')) {
                    axios.delete(route, {
                    })
                    .then(response => {
                        oGui.showOk();
                        window.location.reload(true);
                    })
                    .catch(function (error) {
                        oGui.showError('Error al eliminar el registro');
                    });
                }
            })();
        },

        showModalDays(scheduleDays){
            this.lScheduleDays = scheduleDays;
            $('#scheduleDaysModal').modal('show');
        },

        programarNuevo(){
            if(this.selEmployee != null){
                var route = this.routeProgramming.replace(':id', this.selEmployee);
                window.open(route, '_blank');
            }
        }
    }
})