
@extends("theme.$theme.layout")
@section('title')
Programacion de Turnos
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
@endsection
@section("styles")
<link rel="stylesheet" href="{{asset("assets/css/table.css")}}">
<link rel="stylesheet" href="{{asset("assets/css/tableimprimir.css")}}" media="print">
@endsection
@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Programacion Turnos</h3>
            </div>
            <div class="box-body">
                <div class="tab">
                    <button class="tablinks" onclick="cambioPestana(event, 'Nueva')">Nueva Programacion</button>
                    <button class="tablinks" onclick="cambioPestana(event, 'Antigua')">Programacion Anterior</button>
                    
                  </div>
                  
                  <!-- Tab content -->
                  <div id="Nueva" class="tabcontent">
                    <h3>Programación de Turnos</h3>
                    <div class="row" style="margin:5px">
                      <div class="col-md-6"><button id="nuevo" name="nuevo" disabled onclick="new_shiftprogramming()"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button> <input type="date" id="fechaini" name="fechaini"><input type="date" id="fechafin" name="fechafin" readonly></div>
                      @if(isset($newest))
                        <div class="col-md-3">
                            <button class="btn btn-warning" id="editar" name="editar" onclick="editShift()"><span class="glyphicon glyphicon-edit" aria-hidden="true"> Editar más reciente</span></button>
                        </div>
                      @endif
                    </div>
                    <div class="row">
                        <input type="hidden" value="{{$typeArea}}" id="typeArea">
                        <div class="col-md-3" id="listanueva">
  
                        </div>
                        <div class="col-md-8" id="turnonuevo">
  
                        </div>
                      </div>
                    <br><br>
                    <div class="row" id="calendario">
                      
                    </div> 
                    <input type="hidden" id="weekFlag" name="weekFlag" value=0>
                    <input type="hidden" id="departFlag" name="departFlag" value=0>
                    <input type="hidden" id="pdfFlag" name="departFlag" value=0>
                    @if(isset($newest))
                      <input type="hidden" id="newest" name="newest" value={{$newest->id}}>
                    @endif
                    <div class= "row" style="margin:5px">
                      <div class="col-md-3" id="guardar" style="margin:5px"></div>
                    </div> 
                    <div class="row" style="margin:5px">
                      <div class="col-md-3" id="pdf" style="margin:5px"></div>
                    </div> 
                  </div>
                  
                  <div id="Antigua" class="tabcontent">
                    <div class="row" style="margin:10px">
                      <div class="col-md-3" style="margin:5px">
                        <select id="semana" name="semana">
                          <option value="0">Selecciona Semana</option>
                          @for( $i = 0 ; count($week) > $i ; $i++){
                            <option value={{$week[$i]->id}}>{{$week[$i]->start.' a '.$week[$i]->end}}</option>
                          }
                          @endfor
                        </select>
                      </div>
                      <div class="col-md-2">
                          <button class="btn btn-warning" id="copiar" name="copiar" disabled onclick="copyShift()"><span class="glyphicon glyphicon-copy" aria-hidden="true"> Copiar</span></button>
                      </div>
                      <div class="col-md-2">
                          <button class="btn btn-warning" id="rotar" name="rotar" disabled onclick="rotateShift()"><span class="glyphicon glyphicon-repeat" aria-hidden="true"> Rotar</span></button>
                      </div>
                    </div>
                    <input type="hidden" value="0" id="pastWeek">
                    <div class="row" id="mostrarPdf" style="margin:10px">
                                              
                  </div>
                </div>
    </div>
</div>
@endsection