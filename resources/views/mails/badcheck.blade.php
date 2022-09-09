<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAP Notificación</title>
    <link rel="stylesheet" href="{{ asset("assets/$theme/bower_components/bootstrap/dist/css/bootstrap.min.css") }}">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Notificación CAP
                    </div>
                    <div class="card-body">
                      <h5 class="card-title">{{ str_pad($numEmployee, 5, "0", STR_PAD_LEFT).' - '.strtoupper($employeeName) }}</h5>
                      <p class="card-text">{{ $reason }}</p>
                      <b>{{ "Checada: ".(\Carbon\Carbon::parse($dtDateTime)->format('d-m-Y H:i:s')) }}</b>
                      <p><b>Fuente: </b>{{ $sSource }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>