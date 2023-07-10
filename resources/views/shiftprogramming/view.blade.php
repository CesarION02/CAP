@extends("theme.$theme.layout")
@section('title')
Turno especial
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
    
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/shiftprogramming/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/past_shiftprogramming.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/fechas.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/new_shiftprogramming.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/select.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/cerrar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/cambiodepartamento.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/rows.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/calendario.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/printpdf.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/tdclick.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/guardar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/subirArchivo.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/copiar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/rotar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/shiftprogramming/editar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/orderDate.js")}}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/shiftprogramming/past_shiftprogramming.js")}}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/shiftprogramming/fechas.js")}}" type="text/javascript"></script>


@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Programaciones de turnos</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:turnoespecial"])
                <div class="box-tools pull-right">
                    <a href="{{route('crear_turno_especial')}}" class="btn btn-block btn-success btn-sm">
                        <i class="fa fa-fw fa-plus-circle"></i> Nuevo
                    </a>
                </div>
                <br>
                <br>
                <div class="row">
                    
                </div>
            </div>
            <div class="box-body">
                <div class="row" style="margin:10px">
                    <div class="col-md-2" style="margin:5px">
                      <select id="anio" name="anio">
                        <option value="0">Seleccione año</option>
                        @for( $i = 0 ; count($year) > $i ; $i++){
                          <option value={{$year[$i]->year}}>{{$year[$i]->year}}</option>
                        }
                        @endfor
                      </select>
                    </div>
                    <div class="col-md-3" style="margin:5px" id="selectsemana">
                      <select id="semana" name="semana">
                        <option value="0">Seleccione semana</option>
                      </select>
                    </div>
                    <div class="row" id="mostrarPdf" style="margin:10px">
                                              
                    </div>
                  </div>   
            </div>
        </div>
    </div>
</div>
@endsection