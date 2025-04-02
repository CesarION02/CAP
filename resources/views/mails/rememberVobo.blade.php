<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>
    @if ($type == 'preClose')
        <h2>La prenómina {{ $wayPay }} número {{ $num }} esta próxima a cerrar</h2>
        <p>Quedan {{$days}} {{ $days > 1 ? ' días' : ' día' }} para la fecha de corte de la prenómina {{ $wayPay }} número {{ $num }}</p>
        <p>la fecha de corte es el {{$endDate}}</p>
    @elseif ($type == 'afterClose')
        <h2>La prenómina {{ $wayPay }} número {{ $num }} ha cerrado</h2>
        <p>No has revisado la prenómina {{ $wayPay }} número {{ $num }}</p>
        <p>Han transcurrido {{$days}} {{ $days > 1 ? ' días' : ' día' }} desde el corte de prenómina</p>
        <p>la fecha de corte fue el {{$endDate}}</p>
    @endif
</body>
</html>