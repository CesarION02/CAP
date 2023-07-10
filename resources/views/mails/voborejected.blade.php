<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Visto bueno rechazado</title>
</head>
<body>
    <h2>Este correo es una prueba, favor de hacer caso omiso</h2>
    <p>El visto bueno de la prenómina de {{ $wayPay }} número {{ $prepayrollNum }} ha sido rechazado por {{ $userRejectName }}.</p>
    <p>Comentarios: {{ $reason }}</p>
    {{-- para las fechas:  --}}
    <p>Periodo: <b>{{ $startDate }}</b> - <b>{{ $endDate }}</b>. P. pago: <b>{{ $wayPay }}</b>.</p>
    <p>Por favor, revisa la información en el sistema.</p>
</body>
</html>