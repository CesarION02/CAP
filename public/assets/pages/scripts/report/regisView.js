var app = new Vue({
    el: '#reportApp',
    data: {
        oData: oData,
        vueGui: oGui
    },
    methods: {
        onChangeOp() {
            $(".chosen-select").trigger("chosen:updated");
        },
        getCssClass(oRow) {
            if (oRow.type_id == 2) {
                if ((typeof oRow.events !== 'undefined' && oRow.events.length > 0) || (typeof oRow.isDayOff !== 'undefined' && oRow.isDayOff > 0) || (typeof oRow.isDayOff !== 'undefined' && oRow.isHoliday > 0)) {
                    return "events"
                }
            }
        }
    },
})