<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>
    @if ($type == 'preClose')
        @if ($days > 0 )
            <h2>La fecha de corte de la prenómina {{ $wayPay }} #{{ $num }} es el {{$endDate}}.</h2>
            <p>{{ $days > 1 ? 'Quedan ' : 'Queda ' }} {{$days}} {{ $days > 1 ? ' días' : ' día' }} para el corte.</p>
        @else
            <h2>La fecha de corte de la prenómina {{ $wayPay }} #{{ $num }} es hoy, {{$endDate}}.</h2>
            <p>Favor de revisar y aprobar la prenómina.</p>
        @endif
    @elseif ($type == 'afterClose')
        @if ($days > 1)
            <h2>Te recordamos dar tu Vobo de la prenómina {{ $wayPay }} {{ $num }} que cortó el {{$endDate}}.</h2>
            <p>Han transcurrido {{$days}} {{ $days > 1 ? ' días' : ' día' }} del corte.</p>
        @elseif ($days == 1)
            <h2>Te recordamos dar tu Vobo de la prenómina {{ $wayPay }} {{ $num }} que cortó ayer.</h2>
        @endif
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