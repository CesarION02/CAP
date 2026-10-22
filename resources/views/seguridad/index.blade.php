<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Checador | Login</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="{{asset("assets/$theme/bower_components/bootstrap/dist/css/bootstrap.min.css")}}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset("assets/$theme/bower_components/font-awesome/css/font-awesome.min.css")}}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{asset("assets/$theme/bower_components/Ionicons/css/ionicons.min.css")}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset("assets/$theme/dist/css/AdminLTE.min.css")}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/cap_reloj.png') }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition login-page">
    <img src="{{ asset('images/aeth_logo.png') }}" 
        alt="Logo Cliente"
        style="position: fixed; top: 10px; right: 10px; width: 80px; height: auto; z-index: 999;">
    <div class="login-box">
        <div class="login-logo">
            <a href="{{route('inicio')}}">Control de Acceso y Prenómina</a>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p class="login-box-msg">Inicie su sesión</p>
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <div class="alert-text">
                        @foreach ($errors->all() as $error)
                            <span>{{ $error }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            <form action="{{route('login_post')}}" method="POST" autocomplete="off">
                @csrf
                <div class="form-group has-feedback">
                    <input type="text" name="name" class="form-control" value="{{old('name')}}" placeholder="Nombre de usuario">
                </div>
                <div class="form-group has-feedback">
                    <div class="input-group">
                        <input id="password" type="password" name="password" class="form-control" placeholder="Contraseña">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" id="togglePassword">
                                <i class="fa fa-eye"></i>
                            </button>
                        </span>
                    </div>
                    
                </div>
                <div class="row">
                    <div class="col-xs-8"></div>
                    <!-- /.col -->
                    <div class="col-xs-4">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">Entrar</button>
                    </div>
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('¿Olvidaste tu contraseña?') }}
                                    </a>
                    <!-- /.col -->
                </div>
            </form>
        </div>
        <!-- /.login-box-body -->
    </div>
    <!-- /.login-box -->

    <script src="{{asset("assets/$theme/bower_components/jquery/dist/jquery.min.js")}}"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="{{asset("assets/$theme/bower_components/bootstrap/dist/js/bootstrap.min.js")}}"></script>
</body>
<script>
    $(document).ready(function () {
        $('#togglePassword').on('click', function () {
            const input = $('#password');
            const icon = $(this).find('i');
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            icon.toggleClass('fa-eye fa-eye-slash');
        });
    });
</script>
<footer style="text-align: center; padding: 20px;">
    <div>
        Creado por:<br>
        <img src="{{ asset('images/swap_logo.jpg') }}" alt="Logo SWAP" style="margin-top: 10px; width: 120px; height: auto;">
    </div>
</footer>

</html>