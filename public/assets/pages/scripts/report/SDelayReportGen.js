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
        picked: 'period',
        idEmployee: null
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
            let dates = this.coDates.split('_');
            this.startDate = dates[0];
            this.endDate = dates[1];
            this.payrollNumber = dates[2];
            this.payrollYear = dates[3];
        }
    },
})