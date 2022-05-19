var app = new Vue({
    data: {
        lEmployees: oData.Employees,
        lWorkships: oData.Workships,
        employee: [],
        workship: [],
        turnos: [],
        startDate: null,
        endDate: null,
        weeks: null,
        extraHours: false,
        diasDescanso: [
            {id: '', text: ''},
            {id: '7', text: 'Sin descanso'},
            {id: '0', text: 'Domingo'},
            {id: '1', text: 'Lunes'},
            {id: '2', text: 'Martes'},
            {id: '3', text: 'Miércoles'},
            {id: '4', text: 'Jueves'},
            {id: '5', text: 'Viernes'},
            {id: '6', text: 'Sábado'}
        ],
        descanso: []
    },
    mounted() {
        let self = this;
  
        // inicializas select2
        var opciones = [];
        opciones.push({id: '', text: ''});
        for(var i = 0; i<self.lEmployees.length; i++){
              opciones.push({id: self.lEmployees[i].id, text: self.lEmployees[i].name});
        }
        
        $('#selectEmployees')
          .select2({ 
              placeholder: 'Selecciona empleado',
              data: opciones,
           })
           .on('select2:select', function () {
            self.employee = $("#selectEmployees").select2('data');
          })
        
        for(var i = 0; i<self.lWorkships.length; i++){
            self.turnos.push({id: self.lWorkships[i].id, text: self.lWorkships[i].name});
        }
        
        // $('#selectWorkships')
        //   .select2({
        //       placeholder: 'Selecciona turno',
        //       data: self.turnos,
        //    })
        //    .on('change', function () {
        //         self.workship = $("#selectWorkships").select2('data');
        //   })

        $('#descanso')
          .select2({
              placeholder: 'Selecciona descanso',
              data: self.diasDescanso,
           })
           .on('change', function () {
                self.descanso = $("#descanso").select2('data');
          })

          $("#selectWorkships").select2({
            data: self.turnos,
            tags: true,
          });
          
          $("#selectWorkships").on("select2:select", function (evt) {
            var element = evt.params.data.element;
            var $element = $(element);
            
            window.setTimeout(function () {  
            if ($("#selectWorkships").find(":selected").length > 1) {
              var $second = $("#selectWorkships").find(":selected").eq(-2);
              
              $element.detach();
              $second.after($element);
            } else {
              $element.detach();
              $("#selectWorkships").prepend($element);
            }
            
            $("#selectWorkships").trigger("change");
            }, 1);
            self.workship = $("#selectWorkships").select2('data');
          });
          
          $("#selectWorkships").on("select2:unselect", function (evt) {
            if ($("#selectWorkships").find(":selected").length) {
              var element = evt.params.data.element;
              var $element = $(element);
             $
             ("#selectWorkships").find(":selected").after($element); 
            }
          });
    },
    methods: {
        setDates(date_start, date_end, diff){
            this.startDate = date_start;
            this.endDate = date_end;
            this.weeks = diff;

            $('#selectWorkships').val([]).trigger('change');

            $('#selectWorkships')
                .select2({ 
                    language: {
                        maximumSelected: function(args) {
                            return "solo puedes seleccionar " + diff + " turnos máximo";
                        },
                    },
                    placeholder: 'Selecciona truno',
                    maximumSelectionLength: this.weeks,
                    tags: true,
                })
        }
    }
}).$mount('#genCheck');