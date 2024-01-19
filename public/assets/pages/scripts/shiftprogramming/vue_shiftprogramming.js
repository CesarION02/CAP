var vueApp = new Vue({
    el: '#shiftprogrammingApp',
    data: {
        lEmployees: [],
        lEmployeesAssigment: [],
        lEmployeesWithOutAssigment: [],
        passCalendar: false,
        lIncidences: [],
    },
    watch:{
        passCalendar: function(val){
            if(val){
                console.log(this.lEmployeesWithOutAssigment);
            }
        }
    },
    mounted(){

    },
    methods: {
        setlEmployeesWithOutAssigment(){
            this.lEmployeesWithOutAssigment = this.lEmployees.filter(employee => !this.lEmployeesAssigment.includes(employee));
            this.passCalendar = true;

            this.makeTableEmployeesWithOutAssigment();
            this.makeTableIncidences();
        },

        makeTableEmployeesWithOutAssigment(){
            var divContenedor = document.getElementById('calendario');

            if(divContenedor != undefined){
                let html =  '<br>'
                            + '<h3 style="text-align: center">Colaboradores sin turno asignado</h3>'
                            +   '<table class="customers">'
                            +       '<thead>'
                            +           '<th>Colaborador</th>'
                            +           '<th></th>'
                            +        '</thead>'
                            +       '<tbody>';
    
                for(emp of this.lEmployeesWithOutAssigment){
                    html = html + '<tr>'
                                +   '<td>' + emp.name + '</td>'
                                +   '<td>Sin turno asignado</td>'
                                + '</tr>';
                }
                html = html + '</tbody></table>';
    
                divContenedor.innerHTML += html;
            }
        },

        makeTableIncidences(){
            var divContenedor = document.getElementById('calendario');

            if(divContenedor != undefined){
                let html =  '<br>'
                            + '<h3 style="text-align: center">Colaboradores con incidencias</h3>'
                            +   '<table class="customers">'
                            +       '<thead>'
                            +           '<th>Colaborador</th>'
                            +           '<th>Incidencia</th>'
                            +           '<th>Fechas</th>'
                            +        '</thead>'
                            +       '<tbody>';
    
                for(inc of this.lIncidences){
                    html = html + '<tr>'
                                +   '<td>' + inc.name + '</td>'
                                +   '<td>' + inc.incident + '</td>'
                                +   '<td>' + inc.start_date + ' a ' + inc.end_date + '</td>'
                                + '</tr>';
                }
                html = html + '</tbody></table>';
    
                divContenedor.innerHTML += html;
            }
        }
    }
});