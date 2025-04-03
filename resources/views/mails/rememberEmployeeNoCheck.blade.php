<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>
    @if ($type == 1)
        <h2>No tienes entrada para el {{$date}}</h2>
        <p>aclara con tu jefe directo esta situación</p>
    @elseif ($type == 2)
        <h2>No tienes salida para el {{$date}}</h2>
        <p>aclara con tu jefe directo esta situación</p>
    @elseif ($type == 3)
        <h2>Tienes una falta para el {{$date}}</h2>
        <p>aclara con tu jefe directo esta situación</p>
    @endif

    <div style="border: solid 1px gray; width: 100%;"></div>

    <div>
        <p style="font-size: 75%; display: inline-block;">
            Favor de no responder este mail, fue generado de forma automática.<br>
            CAP © Software Aplicado SA de CV<br>
            www.swaplicado.com.mx<br>
        </p>
    </div>
</body>
</html>