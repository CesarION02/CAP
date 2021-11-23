{{--
    
Ejemplo de implementación:

@include('controls.b-week', ['start_date_v' => null, 'end_date_v' => null,
                            'start_date_name' => 'start_date', 'end_date_name' => 'end_date'])

ó

@include('controls.b-week', ['start_date_v' => '2021-11-01', 'end_date_v' => '2021-11-30',
                            'start_date_name' => 'start_date', 'end_date_name' => 'end_date'])

***********************************************************************************************************************/ 
--}}

<div class="btn-group" data-toggle="buttons">
    <label class="btn btn-primary active">
        <input type="radio" name="options" id="week" value="week" checked> Semana
    </label>
    <label class="btn btn-primary">
        <input type="radio" name="options" id="biweek" value="biweek"> Quincena
    </label>
    <label class="btn btn-primary">
        <input type="radio" name="options" id="biweekcal" value="biweekcal"> Quincena Cal.
    </label>
</div>
<input type="hidden" id="start-date" name="{{ $start_date_name }}">
<input type="hidden" id="end-date" name="{{ $end_date_name }}">
<br>
<br>
<div id="daterange-b-week" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
    <i class="fa fa-calendar"></i>&nbsp;
    <span></span> <i class="fa fa-caret-down"></i>
</div>

@section("last_scripts")
<script type="text/javascript">
    $(function() {
        moment.locale('es');

        let dateStart = <?php echo json_encode($start_date_v) ?>;
        let dateEnd = <?php echo json_encode($end_date_v) ?>;

        let start = dateStart == null ? moment().subtract(7, 'days') : moment(dateStart);
        let end = dateEnd == null ? moment() : moment(dateEnd);

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

            let pType = $('input[name=options]:checked').val();
   
            axios.get(route, {
                params: {
                    "dt_date" : dtDate.format('D-M-YYYY')
                }
            })
            .then(res => {
                weeks = res.data.weeks;
                biweeks = res.data.biweeks;
                biweeksCal = res.data.biweekscal;

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
                    ranges: $(this).val() == "week" ? weekCuts : $(this).val() == "biweek" ? biweekCuts : biweekCalCuts,
                    drops: "auto"
                }, cb);

        $('input[type=radio][name=options]').on('change', function() {
            switch ($(this).val()) {
                case 'week':
                        $('#daterange-b-week').daterangepicker({
                            startDate: start,
                            endDate: end,
                            ranges: weekCuts,
                            drops: "auto"
                        }, cb);
                break;
                case 'biweek':
                        $('#daterange-b-week').daterangepicker({
                            startDate: start,
                            endDate: end,
                            ranges: biweekCuts,
                            drops: "auto"
                        }, cb);
                break;
                case 'biweekcal':
                        $('#daterange-b-week').daterangepicker({
                            startDate: start,
                            endDate: end,
                            ranges: biweekCalCuts,
                            drops: "auto"
                        }, cb);
                break;
            }
        });
    });
</script>
@endsection