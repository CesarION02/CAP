@extends("theme.$theme.layout")

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection

@section('title')
Reporte Retardos
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte de retardos</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route('reporteRetardos') }}">
                <div class="box-body" id="reportApp">
                    <div class="row">
                        <div class="col-md-2">
                            Por:
                        </div>
                        <div class="col-md-4 col-md-offset-2">
                            <select name="pay_way" id="" class="form-control">
                                <option value="2">Semana</option>
                                <option value="1">Quincena</option>
                                <option value="0">Todos</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-2">
                            Rango de fechas:
                        </div>
                        <div class="col-md-6 col-md-offset-2">
                            <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                        <input value="2020-02-18" type="hidden" name="start_date">
                        <input value="2020-02-27" type="hidden" name="end_date">
                    </div>
                </div>
                <div class="box-footer">
                    <div class="col-lg-8"></div>
                    <div class="col-lg-2">
                        <button class="btn btn-warning" id="generar" name="generar" type="submit">Generar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(function() {
        
            var start = moment('2020-02-18');
            var end = moment('2020-02-27');
        
            function cb(start, end) {
                $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            }
        
            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                   'Today': [moment(), moment()],
                   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);
        
            cb(start, end);
        
        });
    </script>
@endsection