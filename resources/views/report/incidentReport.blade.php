@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
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
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:reporteincidencias"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route('reporteIncidenciasGenerar') }}">
                
                <div class="box-body" id="reportApp">
                    <div class="row">
                        <div class="col-md-5 col-md-offset-1">
                            <label for="start_date">Fecha inicial:</label>
                            <input type="date" name="start_date" id="start_date" required>
                        </div>
                        <div class="col-md-5">
                            <label for="end_date">Fecha final:</label>
                            <input type="date" name="end_date" id="end_date" required>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1">
                            <label for="cars">Elige tipo incidencia:</label>
                            
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="incidents" name="incidents" required>
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
                            
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select tipo" id="tipo" name="tipo" required>
                                
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
                            
                            <select disabled="true" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="dept" name="dept" required>
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
                            
                            <select disabled="true" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" id="employees" name="employees" required>
                                    <option value="0">Todos</option>
                                @for($i = 0 ; count($employees) > $i ; $i++)
                                    <option value="{{$employees[$i]->id}}">{{$employees[$i]->name.' - '.$employees[$i]->num_employee}}</option>
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