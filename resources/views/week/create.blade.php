@extends("theme.$theme.layout")
@section('title')
    Turnos
@endsection

@section("scripts")
<script>
    $(document).on('change', '#year', function() {
        var year = document.getElementById("year").value; 
        $.ajax({
            type: 'get',
            url: 'firts',
            data: { 'year': year },
        success: function(data) {
            var lista = '<input id="dia" name="dia" value="'+data[0].dt_date+'">'
            $("#primerdia").append(lista);
        },
        error: function() {
            console.log('falle');
        }
        });   
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Crear turno</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('turno')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{route('guardar_semana')}}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="año" class="col-lg-3 control-label requerido">Año:</label>
                        <div class="col-lg-8">
                            <select class="form-control" name="year" id="year">
                                <?php
                                for ($year = ((int)date('Y')+1); 2000 <= $year; $year--): ?>
                                  <option value="<?=$year;?>"><?=$year;?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dia" class="col-lg-3 control-label requerido">Primer dia:</label>
                        <div class="col-lg-8" id="primerdia">

                        </div>
                    </div>    
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button type="submit" class="btn btn-success">Generar</button>
                        <button type="reset" class="btn btn-default">Deshacer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection