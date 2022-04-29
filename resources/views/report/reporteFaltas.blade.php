@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
    <link rel="stylesheet" href="https://jsuites.net/v4/jsuites.css" type="text/css" />
@endsection
@section('title')
Reporte faltas
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte faltas</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <form id="form" action="{{ route('reporteFaltasGenerar') }}">
                <div class="box-body" id="reportApp">
                    <div class="row">
                        <div class="col-md-12 col-md-offset-1">
                            <label style = "float: left; height: 100%;">Fecha inicio:</label>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-12 col-md-offset-1">
                                        <div class="col-md-4">
                                            <input id='calendarStart' name="calendarStart" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <label style = "float: left; height: 100%;">Fecha final:</label>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-12 col-md-offset-1">
                                        <div class="col-md-4">
                                            <input id='calendarEnd' name="calendarEnd" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-1">
                            <label for="tipo">Generar reporte por:</label>
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select tipo" id="tipo" name="tipo" required> 
                                <option value="">Selecciona opciones..</option>
                                <option value="1">Por departamento</option>
                                <option value="2">Por empleado</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-1">
                            <label for="dept">Elige departamento:</label>
                            
                            <select disabled="true" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="dept" name="dept" required>
                                @for($i = 0 ; count($deptos) > $i ; $i++)
                                    <option value="{{$deptos[$i]->id}}">{{$deptos[$i]->name}}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6 col-md-offset-1" id='selectemp'>
                            <label for="employees">Elige empleado:</label>
                            
                            <select disabled="true" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="employees" name="employees" required>
                                    <option value="0">Todos</option>
                                @for($i = 0 ; count($employees) > $i ; $i++)
                                    <option value="{{$employees[$i]->id}}">{{$employees[$i]->name}}</option>
                                @endfor   
                            </select>
                        </div>
                    </div>
                    
                    
                </div>
                <div class="box-footer">
                    <div class="" style="float: right;">
                        <button id="generar" class="btn btn-warning" type="submit">Generar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src={{ asset("myMonthPicker/pickerMonth.js") }}></script>
    <script>
        function addLeadingZeros(n) {
            if (n <= 9) {
                return "0" + n;
            }
            return n
        }
        var cs = jSuites.calendar(document.getElementById('calendarStart'), {
                        type: 'year-month-picker',
                        format: 'MMM-YYYY',
                        onchange: function(instance, value) {
                            var d1 = Date.parse(value);
                            var mIni = new Date(d1);
                            ce.options.validRange = [ mIni.getFullYear() + "-" + addLeadingZeros(mIni.getMonth() + 1) + "-" + addLeadingZeros(mIni.getDate()), '2099-12-31' ];
                        }
                    });

        var ce = jSuites.calendar(document.getElementById('calendarEnd'), {
                    type: 'year-month-picker',
                    format: 'MMM-YYYY',
                    validRange: [ '2099-12-31', '2099-12-31' ]
                });
    </script>
    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/report/typeReport.js")}}" type="text/javascript"></script>
    <script>
        $(".chosen-select").chosen();
    </script>
    <script>
        $(document).ready(function(){
            var fm = document.getElementById('form');
            fm.addEventListener('submit', (e) => {
                e.preventDefault();
                e.stopPropagation();
                document.getElementById('generar').setAttribute('disabled', 'disabled');
                if(document.getElementById('calendarStart').value != "" && document.getElementById('calendarEnd').value != ""){
                    $(this).off('submit').submit();
                    fm.submit();
                }else{
                    swal({
                        title: "Debe seleccionar un rango de fecha.",
                        icon: "warning"
                    })
                    document.getElementById('generar').removeAttribute('disabled');
                }
            });
        });
    </script>
@endsection