<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAP Notificación</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; background-color: #fff; color: #333;">
    <div style="max-width: 600px; margin: 20px auto; background-color: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h1 style="font-size: 20px; color: #0056b3; text-align: center; margin-bottom: 10px;">
            Reporte de Tiempo Laboral e Incidencias
        </h1>
        <h2 style="font-size: 16px; color: #333; text-align: center; margin-bottom: 20px;">
            {{ "Personal de " . strtolower($sPayTypeText) . "" }}
        </h2>
        <h3 style="text-align: center;">
            {{ "Periodo tiempo retardo y tiempo adicional " }}
            <br>
            <b style="font-size: 16px; color: #214cda; margin: 5px 0; text-align: center;">{{ $sPeriod }}</b>
        </h3>
        <h3 style="text-align: center;">
            {{ "Incidencias del " }}
            <br>
            <b style="font-size: 16px; color: #214cda; margin: 5px 0; text-align: center;">
                {{ (\App\SReport\SReportUtils::formatRange($incidentsStart, $incidentsEnd)) . " (últimos " . $monthsAgo . " meses)" }}
            </b>
        </h3>
        <br>

        @if (count($lData) == 0)
            <h3 style="font-size: 14px; color: #555; text-align: center;">No hay información qué mostrar.</h3>
        @else
            @foreach ($lData as $oEmp)
                <div style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #000;">
                    <h3 style="font-size: 16px; color: #0056b3; margin: 0;">
                        {{ $oEmp->numEmployee . " - " . $oEmp->employee }}
                    </h3>
                    <p style="font-size: 14px; color: #214cda; margin: 5px 0;">
                        <b>{{ ucfirst($oEmp->departmentName) }}</b>
                    </p>
                    <table>
                        <tr style="font-size: 14px; color: #555; margin: 5px 0;">
                            <td>Horario:</td>
                            <td><b>{{ \App\SUtils\SDelayReportUtils::removeSeconds($oEmp->schedule) }}</b></td>
                        </tr>
                        <tr style="font-size: 14px; margin: 5px 0;">
                            <td>Retardo acumulado:</td>
                            <td style="color: {{ $oEmp->totalDelay > 15 ? 'red' : '#333' }};">
                                <b>{{ \App\SUtils\SDelayReportUtils::convertToHoursMinsText($oEmp->totalDelay) }}</b>
                            </td>
                        </tr>
                        <tr style="font-size: 14px; margin: 5px 0;">
                            <td>Tiempo adicional:</td>
                            <td>
                                <b>{{ \App\SUtils\SDelayReportUtils::convertToHoursMinsText($oEmp->totalAditional) }}</b>
                            </td>
                        </tr>
                    </table>
                    @if ($oEmp->totalDelay > 15)
                        <p style="font-size: 14px; color: red; margin-top: 10px;">
                            Nota: Únicamente se permiten 15 minutos acumulados de retardo en una quincena.
                        </p>
                    @endif
                    @if (count($oEmp->aIncidents) > 0)
                        <hr style="border: none; border-top: 1px dashed #ddd; margin: 10px 0;">
                        <h3 style="font-size: 14px; color: #333; margin-top: 10px;">Incidencias:</h3>
                        <ul style="padding-left: 20px; margin: 5px 0;">
                            @foreach ($oEmp->lAdjusts as $oResume)
                                @if (!isset($oResume->counter) || $oResume->counter == 0)
                                    @continue
                                @endif
                                <li style="font-size: 14px; color: #555; margin-bottom: 5px;">
                                    {{ $oResume->text ?? '' }}: 
                                    <b>{{ $oResume->counter ?? '' }}</b> {{ $oResume->unit ?? '' }}
                                    @if (isset($oResume->lDays) && count($oResume->lDays) > 0)
                                        <ul style="padding-left: 20px; margin: 5px 0;">
                                            @foreach ($oResume->lDays as $sDay)
                                                <li style="font-size: 14px; color: #555; margin-bottom: 5px;">
                                                    {{ $sDay }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                            @foreach ($oEmp->aIncidents as $oResume)
                                @if (!isset($oResume->counter) || $oResume->counter == 0)
                                    @continue
                                @endif
                                <li style="font-size: 14px; color: #555; margin-bottom: 5px;">
                                    {{ $oResume->text ?? '' }}: 
                                    <b>{{ $oResume->counter ?? '' }}</b> {{ $oResume->unit ?? '' }}
                                    @if (count($oResume->lDays) > 0)
                                        <ul style="padding-left: 20px; margin: 5px 0;">
                                            @foreach ($oResume->lDays as $sDay)
                                                <li style="font-size: 14px; color: #555; margin-bottom: 5px;">
                                                    {{ $sDay }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
    <br>
    @include('mails.footer')
</body>

</html>