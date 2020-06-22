var app = new Vue({
    el: '#reportDelayApp',
    data: {
        oData: oData,
        vueGui: oGui
    },
    methods: {
        getCssClass(oRow, report) {
            if (oRow.ischeckschedule) {
                return 'check';
            }
            if (report != this.oData.REP_DELAY) {
                if (oRow.entryDelayMinutes > 0) {
                    return 'danger';
                }
            }
        }
    },
})