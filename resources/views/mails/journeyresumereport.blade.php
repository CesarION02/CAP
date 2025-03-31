<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAP Notificación</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; color: #333;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h1 style="font-size: 20px; color: #0056b3; text-align: center; margin-bottom: 10px;">
            Reporte de Tiempo Laboral e Incidencias
        </h1>
        <h2 style="font-size: 16px; color: #333; text-align: center; margin-bottom: 20px;">
            {{ "Período: " . $sPeriod }}
        </h2>
        <h2 style="font-size: 16px; color: #333; text-align: center; margin-bottom: 20px;">
            {{ "(Incidencias de los últimos 30 días)" }}
        </h2>

        @if (count($lData) == 0)
            <h3 style="font-size: 14px; color: #555; text-align: center;">No hay información qué mostrar.</h3>
        @else
            @foreach ($lData as $oEmp)
                <div style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                    <h3 style="font-size: 16px; color: #0056b3; margin: 0;">
                        {{ $oEmp->numEmployee . " - " . $oEmp->employee }}
                    </h3>
                    <p style="font-size: 14px; color: #555; margin: 5px 0;">
                        Departamento: <b>{{ ucfirst($oEmp->departmentName) }}</b>
                    </p>
                    <p style="font-size: 14px; color: #555; margin: 5px 0;">
                        Horario: <b>{{ $oEmp->schedule }}</b>
                    </p>
                    <p style="font-size: 14px; margin: 5px 0;">
                        Retardo acumulado: 
                        <b style="color: {{ $oEmp->totalDelay > 15 ? 'red' : '#333' }};">
                            {{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oEmp->totalDelay) }}
                        </b>
                    </p>
                    <p style="font-size: 14px; margin: 5px 0;">
                        Tiempo adicional: 
                        <b>{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oEmp->totalAditional) }}</b>
                    </p>

                    @if (count($oEmp->aIncidents) > 0)
                        <h4 style="font-size: 14px; color: #333; margin-top: 10px;">Incidencias:</h4>
                        <ul style="padding-left: 20px; margin: 5px 0;">
                            @foreach ($oEmp->aIncidents as $oResume)
                                @if (!isset($oResume->counter) || $oResume->counter == 0)
                                    @continue
                                @endif
                                <li style="font-size: 14px; color: #555; margin-bottom: 5px;">
                                    {{ $oResume->text ?? '' }}: 
                                    <b>{{ $oResume->counter ?? '' }}</b> {{ $oResume->unit ?? '' }}
                                    @if (count($oResume->lDays) > 0)
                                        <ul style="padding-left: 20px; margin: 5px 0;">
                                            @foreach ($oResume->lDays as $oDay)
                                                <li style="font-size: 14px; color: #555; margin-bottom: 5px;">
                                                    {{ isset($oDay['date']) ? \Carbon\Carbon::parse($oDay['date'])->isoFormat('D MMMM YYYY') : '' }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($oEmp->totalDelay > 15)
                        <p style="font-size: 14px; color: red; margin-top: 10px;">
                            Nota: Únicamente se permiten 15 minutos acumulados de retardo en una quincena.
                        </p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</body>

</html>