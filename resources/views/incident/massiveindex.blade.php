@extends("theme.$theme.layout")
@section('title')
Incidencias
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        td{
            text-align: center;
        }
        
        td:hover{
            cursor: pointer;
        }
        
        #copy{
            background: green;
            color: white;
            cursor: pointer;
            display: inline-block;	
            padding: .24rem .5rem;
            border-radius: .5rem;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -o-user-select: none;
        }

        #todo{
            background: green;
            color: white;
            cursor: pointer;
            display: inline-block;	
            padding: .24rem .5rem;
            border-radius: .5rem;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -o-user-select: none;
        }
        
        #regreso{
            background: red;
            color: white;
            cursor: pointer;
            display: inline-block;	
            padding: .24rem .5rem;
            border-radius: .5rem;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -o-user-select: none;
        }
        
        #copy:hover{
            filter: brightness(1.2);
            -webkit-filter: brightness(1.2);
            -moz-filter: brightness(1.2);
            -o-filter: brightness(1.2);
        }

        #todo:hover{
            filter: brightness(1.2);
            -webkit-filter: brightness(1.2);
            -moz-filter: brightness(1.2);
            -o-filter: brightness(1.2);
        }

        #regreso:hover{
            filter: brightness(1.2);
            -webkit-filter: brightness(1.2);
            -moz-filter: brightness(1.2);
            -o-filter: brightness(1.2);
        }
    </style>
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
<script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
<script src="{{ asset('dt/jszip.min.js') }}"></script>
<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
<script src="{{ asset('dt/buttons.print.min.js') }}"></script>
<script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
<script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>

<script>
    var oneTbody = document.querySelector("#one tbody"),
	twoTbody = document.querySelector("#two tbody"),
	copy = document.querySelector("#copy"),
    todo = document.querySelector('#todo'),
    regreso = document.querySelector('#regreso'),
	seleccion = [],
    empleados = [],
	seleccionar = function(event){
		if (event.target.tagName == "TD"){
			var fila = event.target.parentNode;
			
			if (fila.dataset.selected < 1){
				fila.style.backgroundColor = "red";
				fila.style.color = "white";
				fila.dataset.selected = 1;
				seleccion.push(fila);
			}
			else{
				fila.style.backgroundColor = "";
				fila.style.color = "";
				fila.dataset.selected = 0;
				seleccion.splice(seleccion.indexOf(fila), 1);				
			}			
		}
	},
	copiar = function(){

		if (seleccion.length){
			for (var i = 0, l = seleccion.length; i < l; i++){
				var tr = twoTbody.insertRow(),
					celdas = seleccion[i].querySelectorAll("td");

				for (var j = 0, m = celdas.length; j < m; j++){
					var td = tr.insertCell();				
					td.outerHTML = celdas[j].outerHTML;
                    var corte = celdas[j].innerHTML;
                    corte = corte.replace(",",";");
                    empleados.push(corte)
				}

				seleccion[i].remove();
                document.getElementById("empleados").value = empleados.join();

                
			}

			seleccion.length = 0;
		}
	},
    all = function(){
        var filas = document.querySelectorAll("#one tbody tr");
 
        [].forEach.call(filas, function(fila){
            seleccion.push(fila);
        });

        for (var i = 0, l = seleccion.length; i < l; i++){
            var tr = twoTbody.insertRow(),
                celdas = seleccion[i].querySelectorAll("td");

            for (var j = 0, m = celdas.length; j < m; j++){
                var td = tr.insertCell();				
                td.outerHTML = celdas[j].outerHTML;
                var corte = celdas[j].innerHTML;
                corte = corte.replace(",",";");
                empleados.push(corte)
            }

            seleccion[i].remove();
            document.getElementById("empleados").value = empleados.join();

            
        }

        seleccion.length = 0;

    },
    regresoall = function(){
        var filas = document.querySelectorAll("#two tbody tr");
 
        [].forEach.call(filas, function(fila){
            seleccion.push(fila);
        });
        for (var i = 0, l = seleccion.length; i < l; i++){
            var tr = oneTbody.insertRow(),
                celdas = seleccion[i].querySelectorAll("td");

            for (var j = 0, m = celdas.length; j < m; j++){
                var td = tr.insertCell();				
                td.outerHTML = celdas[j].outerHTML;
            }

            seleccion[i].remove();
            document.getElementById("empleados").value = ""; 
        }
        empleados = [];
        seleccion.length = 0;    
    };
	
oneTbody.addEventListener("click", seleccionar, false);
twoTbody.addEventListener("click", seleccionar, false);
copy.addEventListener("click", copiar, false);  
todo.addEventListener("click", all, false);
regreso.addEventListener("click", regresoall, false);
</script>
@endsection
@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">
                    Asignación masiva incidencias   
                </h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:nolaborables"])
                <div class="row">
                    <div class="col-md-5 col-md-offset-7">
                        
                    </div>
                </div>
            </div>
            <form action="{{route('guardar_masivo')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
            <div class="box-body">
                <div class="form-group">
                    <label for="Tipoincidente" class="col-lg-3 control-label requerido">Tipo incidencia:</label>
                    <div class="col-lg-8">
                        <select id="type_incidents_id" name="type_incidents_id" class="form-control">
                            @foreach($incidents as $type => $index)
                            <option value="{{ $index }}" {{old('type_incidents_id') == $index ? 'selected' : '' }}> {{$type}}</option>
                            @endforeach
                        </select>
                
                    </div>
                </div>
                <div class="form-group">
                    <label for="start_date" class="col-lg-3 control-label requerido">Fecha inicial:</label>
                    <div class="col-lg-8">
                        <input type="date" class="form-control" name="start_date" id="start_date"class="form-control">
                    </div>
                </div>
                <div class="form-group">
                        <label for="end_date" class="col-lg-3 control-label requerido">Fecha final:</label>
                        <div class="col-lg-8">
                            <input type="date" class="form-control" name="end_date" id="end_date"  class="form-control">
                        </div>
                </div>
                <div class="row">
                    <div class = "col-sm-6">
                        <table style="width:90%" id="one">
                            <thead>
                                <tr>
                                    <th class = "text-center">Nombre empleado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                <tr data-selected="0">
                                    <?php $nombre = str_replace(array(","), ',', $employee->name); ?>
                                    <td class = "text-left">{{$nombre}}</td>
                                </tr>
                                    
                                @endforeach
                            </tbody>
                        </table>
                        
                    </div> 
                    
                    <div class = "col-sm-6">
                        <table style="width:90%" id="two">
                            <thead>
                                <tr>
                                    <th class = "text-center">Nombre empleado</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div> 
                <input type="hidden" value="" name="empleados" id="empleados">
                <br>
                <div class="row">
                    <div class="col-lg-8">
                        <span id="copy">Pasar a derecha ></span>
                        <span id="todo">Pasar a derecha todos >></span>
                        <span id="regreso">Pasar a izquierda todos <<</span>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-lg-8">
                        <button type="submit" class="btn btn-success" id="guardar">Guardar</button>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection