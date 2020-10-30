@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
@endsection
@section('title')
Procesar datos
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                    <h3 class="box-title">Procesar periodo</h3>
                   
                
                <div class="box-tools pull-right">
                </div>
            </div>
            <form action="{{ route('guardar_periodo') }}" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body" id="reportApp">
                    <div class="row">
                        <div class="col-md-5 col-md-offset-1">
                            <label for="start_date">Fecha inicial:</label>
                            <input type="date" name="start_date" id="start_date" :value="startDate">
                        </div>
                        <div class="col-md-5">
                            <label for="end_date">Fecha final:</label>
                            <input type="date" name="end_date" id="end_date" :value="endDate">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-5 col-md-offset-1">
                            <label for="start_date">Periodicidad pago:</label>
                            <select name="way_pay" id="way_pay">
                                <option value = 2> Semanal</option>
                                <option value = 1> Quincenal</option>
                            </select>
                        </div>    
                    </div>
                    
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button class="btn btn-warning" id="generar" name="generar" type="submit">Procesar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    
@endsection