var app = new Vue({
    el: '#reportDelayApp',
    data: {
        oData: oData,
        vueGui: oGui
    },
    methods: {
        getCssClass(oRow, report) {
            if (oRow.hasAbsence || !oRow.hasCheckOut || !oRow.hasCheckIn) {
                return 'absence';
            }
            if (oRow.isCheckSchedule) {
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