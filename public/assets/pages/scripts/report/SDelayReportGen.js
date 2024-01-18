var app = new Vue({
    el: '#reportDelayAppGen',
    data: {
        lEmps: oData.lEmployees,
        oPayrolls: oData.oPayrolls == undefined ? [] : oData.oPayrolls,
        coDates: "0_0_0_0",
        iPayWay: 2,
        startDate: '',
        endDate: '',
        payrollNumber: 0,
        payrollYear: 0,
        idDelegation: 0,
        picked: 'period',
        idEmployee: null,
        oDelegation: {}
    },
    watch: {
        iPayWay: function(val) {
            switch (val) {
                case "1":
                    document.getElementById("biweek").click();
                    break;
                case "2":
                    document.getElementById("week").click();
                    break;
                default:
                    break;
            }
        }
    },
    methods: {
        setDates(sd, ed) {
            this.startDate = sd;
            this.endDate = ed;
        },
        onFilterTypeChange() {
            this.iPayWay = 2;
        },
        onCutoffDateChange() {
            this.startDate = this.oDelegation.start_date;
            this.endDate = this.oDelegation.end_date;
            this.payrollNumber = this.oDelegation.number;
            this.payrollYear = this.oDelegation.year;
            this.idDelegation = this.oDelegation.id_delegation;
        }
    },
})