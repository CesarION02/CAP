@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
@endsection
@section('title')
Reporte incidencias
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte incidencias</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route('reporteIncidenciasGenerar') }}">
                
                <div class="box-body" id="reportApp">
                    <div class="row">
                        <div class="col-md-5 col-md-offset-1">
                            <label for="start_date">Fecha inicial:</label>
                            <input type="date" name="start_date" id="start_date">
                        </div>
                        <div class="col-md-5">
                            <label for="end_date">Fecha final:</label>
                            <input type="date" name="end_date" id="end_date">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1">
                            <label for="cars">Elige tipo incidencia:</label>
                            
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="incidents" name="incidents">
                                <option value="0">Todos</option>
                                @for($i = 0 ; count($incidents) > $i ; $i++)
                                    <option value="{{$incidents[$i]->id}}">{{$incidents[$i]->name}}</option>
                                @endfor   
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1">
                            <label for="cars">Elige:</label>
                            
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select tipo" id="tipo" name="tipo">
                                
                                <option value="0">Selecciona opciones..</option>
                                <option value="1">Por departamento</option>
                                <option value="2">Por empleado</option>
                                  
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1">
                            <label for="cars">Elige departamento:</label>
                            
                            <select disabled="true" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="dept" name="dept">
                                @for($i = 0 ; count($deptos) > $i ; $i++)
                                    <option value="{{$deptos[$i]->id}}">{{$deptos[$i]->name}}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1" id='selectemp'>
                            <label for="cars">Elige empleado:</label>
                            
                            <select disabled="true" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="employees" name="employees">
                                    <option value="0">Todos</option>
                                @for($i = 0 ; count($employees) > $i ; $i++)
                                    <option value="{{$employees[$i]->id}}">{{$employees[$i]->name}}</option>
                                @endfor   
                            </select>
                        </div>
                    </div>
                    
                    
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button class="btn btn-warning" id="generar" name="generar" type="submit">Generar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src="{{ asset("assets/pages/scripts/report/generar.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/report/typeReport.js")}}" type="text/javascript"></script>
    <script>
        $(".chosen-select").chosen();
    </script>
@endsection