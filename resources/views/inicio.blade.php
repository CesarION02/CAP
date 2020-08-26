@extends("theme.$theme.layout")
@section('title')
Bienvenido
@endsection
@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h2 class="box-title">Bienvenido {{session()->get('name')}}</h2>
            </div>
            <div class="box-body">
                <div class="form-group"><div class="col-md-12"><center><h2></h2></center></div></div>
                <br><br>
                <br><br>
                <?php $contador = 0 ; ?>
                    @while(count($datas) > $contador)
                        <div class="form-group">
                            <?php $contadorAux = 0 ; ?>
                            @while($contadorAux < 3)
                                @if($contador < count($datas))
                                    <div class="col-md-4">
                                        <?php $icono = "fa fa-fw ".$datas[$contador]->icono; ?>
                                        <a href="{{url($datas[$contador]->url)}}" class="btn btn-block btn-primary btn-lg"><i class="{{$icono}}"></i> {{ $datas[$contador]->nombreMenu}} </a>
                                    </div>
                                    
                                @endif
                                <?php $contadorAux ++; ?>
                                <?php $contador ++; ?>
                            @endwhile
                        </div> 
                        <br><br>
                        <br> 
                    @endwhile
                   
            </div>
        </div>
    </div>
</div>
@endsection