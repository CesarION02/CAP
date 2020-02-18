@extends("theme.$theme.layout")
@section('title')
Reporte Entradas/Salidas
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/report/generar.js")}}" type="text/javascript"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte Entradas/Salidas</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
                <div class="box-body">
                <input type="hidden" id="type" name="type" value={{$type}}>
                @if($type == 1)
                    <div class="row">
                        <label for="departamento" class="col-lg-3 control-label">Departamentos:</label>
                        <div class="col-lg-8">              
                            <select name="departamento" id="departamento">  
                                <option value=0>Seleccionar Departamento</option>
                                @foreach($departments as $department => $index)    
                                    <option value="{{$index}}">{{$department}}</option>
                                @endforeach
                            </select>
                        </div>
        
                    </div>  
                @else
                    <div class="row">
                        <label for="empleado" class="col-lg-3 control-label">Departamentos:</label>
                        <div class="col-lg-8">              
                            <select name="empleado" id="empleado">  
                                <option value=0>Seleccionar Empleado</option>
                                @foreach($employees as $employee)    
                                    <option value="{{$employee->idEmp}}">{{$employee->nameEmp}}</option>
                                @endforeach
                            </select>
                        </div>
    
                    </div> 
                @endif 
                    <div class="row">
                        <label for="start_date" class="col-lg-3 control-label">Fecha Inicio:</label>
                        <div class="col-lg-3">
                            <input type="date" name="start_date" id="start_date">
                        </div>  
                        <label for="end_date" class="col-lg-2 control-label">Fecha Fin:</label>
                        <div class="col-lg-3">
                            <input type="date" name="end_date" id="end_date">
                        </div> 
                    </div>
                
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button class="btn btn-warning" id="generar" name="generar" onclick="generar()">Generar</button>
                    </div>
                </div>
            
        </div>
    </div>
</div> 
@endsection