<input type="hidden" id="start-date" name="{{ $start_date_name }}">
<input type="hidden" id="end-date" name="{{ $end_date_name }}">
<div id="daterange-b-week" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
    <i class="fa fa-calendar"></i>&nbsp;
    <span></span> <i class="fa fa-caret-down"></i>
</div>

@section("last_scripts")
<script type="text/javascript">
    $(function() {
        moment.locale('es');

        var s = '<?php echo $start_date; ?>';
        var e = '<?php echo $end_date; ?>';
        
        let dateStart = null;
        let dateEnd = null;
        if(s != "" && e != ""){
            dateStart = moment(s + "T00:00:00", 'YYYY-MM-DD');
            dateEnd = moment(e + "T00:00:00", 'YYYY-MM-DD');
        }

        let start = dateStart == null ? moment().startOf('month') : dateStart;
        let end = dateEnd == null ? moment().endOf('month') : dateEnd;

        var weekCuts = {};
        var biweekCuts = {};
        var biweekCalCuts = {};

        setRanges(start, start, end);

        cb(start, end);

        function cb(start, end) {
            $('#daterange-b-week span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));

            let start_d = document.getElementById("start-date");
            let end_d = document.getElementById("end-date");
                
            start_d.setAttribute('value', start.format('YYYY-MM-DD'));
            end_d.setAttribute('value', end.format('YYYY-MM-DD'));
        }

        function setRanges(dtDate, start, end) {
            let route = <?php echo json_encode(route('getcuts')) ?>;
            let weeks = [];
            let biweeks = [];
            let biweeksCal = [];

            let pType = '<?php echo $idPreNomina; ?>';
   
            axios.get(route, {
                params: {
                    "year" : dtDate.format('YYYY')
                }
            })
            .then(res => {
                weeks = res.data.weeks.reverse();
                biweeks = res.data.biweeks.reverse();
                biweeksCal = res.data.biweekscal.reverse();

                for (const w of weeks) {
                    weekCuts["(" + w.number + ") - " + moment(w.dt_start).format('DD/MM/YYYY') + "-" + moment(w.dt_end).format('DD/MM/YYYY')] = 
                        [moment(w.dt_start), moment(w.dt_end)];
                }

                for (const b of biweeks) {
                    biweekCuts["(" + b.number + ") - " + moment(b.dt_start).format('DD/MM/YYYY') + "-" + moment(b.dt_end).format('DD/MM/YYYY')] = 
                        [moment(b.dt_start), moment(b.dt_end)];
                }

                for (const b of biweeksCal) {
                    biweekCalCuts["(" + b.number + ") - " + moment(b.dt_start).format('DD/MM/YYYY') + "-" + moment(b.dt_end).format('DD/MM/YYYY')] = 
                        [moment(b.dt_start), moment(b.dt_end)];
                }

                $('#daterange-b-week').daterangepicker({
                    startDate: start,
                    endDate: end,
                    maxDate: moment().add(1, 'days'),
                    ranges: pType == "week" ? weekCuts : pType == "biweek" ?  biweekCuts : biweekCalCuts,
                    drops: "auto"
                }, cb);
            })
            .catch(function(error) {
                console.log(error);
            });
        }

        $('#daterange-b-week').daterangepicker({
                    startDate: start,
                    endDate: end,
                    maxDate: moment().add(1, 'days'),
                    ranges: $(this).val() == "week" ? weekCuts : $(this).val() == "biweek" ? biweekCuts : biweekCalCuts,
                    drops: "auto"
                }, cb);

        $('input[type=radio][name=options]').on('change', function() {
            switch ($(this).val()) {
                case 'week':
                        $('#daterange-b-week').daterangepicker({
                            startDate: start,
                            endDate: end,
                            maxDate: moment().add(1, 'days'),
                            ranges: weekCuts,
                            drops: "auto"
                        }, cb);
                break;
                case 'biweek':
                        $('#daterange-b-week').daterangepicker({
                            startDate: start,
                            endDate: end,
                            maxDate: moment().add(1, 'days'),
                            ranges: biweekCuts,
                            drops: "auto"
                        }, cb);
                break;
                case 'biweekcal':
                        $('#daterange-b-week').daterangepicker({
                            startDate: start,
                            endDate: end,
                            maxDate: moment().add(1, 'days'),
                            ranges: biweekCalCuts,
                            drops: "auto"
                        }, cb);
                break;
            }
        });
    });
</script>
@endsection