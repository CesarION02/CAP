@extends("theme.$theme.layout")
@section('title')
    Mis Empleados
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @include('includes.mensaje')
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Mis Empleados</h3>
                    @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php"])
                </div>
                <div class="box-body">
                    <div class="row">
                        @foreach ($lPhotos as $photo)
                            <div class="col-md-4">
                                <div class="thumbnail">
                                    @if ($photo->photo == null)
                                        <img style="width:100px;height:100px;" width="100" height="100" src="{{ asset('images/user-icon.jpg') }}" />
                                    @else
                                        {!!  '<img style="width:100px;height:100px;" src="data:image/jpg;base64, ' . $photo->photo . '" />' !!}
                                    @endif
                                    <div class="caption">
                                        <h3>Num: <b>{{ $photo->numEmployee }}</b></h3>
                                        <p>{{ $photo->name }}</p>
                                        <p><a href="#" class="btn btn-primary" role="button">Button</a> <a href="#" class="btn btn-default" role="button">Button</a></p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection