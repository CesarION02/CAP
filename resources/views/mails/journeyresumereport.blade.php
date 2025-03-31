<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAP Notificación</title>
</head>

{{-- <body style="font-family: 'Courier New', monospace"> --}}
{{-- <body style="font-family: 'Georgia, serif'"> --}}
<body style="font-family: 'Arial, Helvetica, sans-serif'">
    <div>
        <h1>Reporte de tiempo laboral e incidencias
            {{-- <b>({{ $typePay }})</b> --}}
        </h1>
        <h2>{{ "Período: " . $sPeriod }}</h2>

        @if (count($lData) == 0)
            <h3>No hay información qué mostrar.</h3>
        @else
            <?php
                $i = 1;
            ?>
            @foreach ($lData as $oEmp)
                    <div style="line-height: 75%">
                        <h3><b>{{ ($oEmp->numEmployee." - ".$oEmp->employee) }}</b> - {{ (ucfirst($oEmp->departmentName)) }}</h4>
                        <h4>Horario: <b>{{ $oEmp->schedule }}</b></h5>
                    </div>
                    <table>
                        <tbody>
                            <tr>
                                <td colspan="5"><b>{{ $oEmp->numEmployee." - ".$oEmp->employee }}</b></td>
                                <td style="text-align: right; padding-left: 8px; padding-right: 8px; 
                                    @if ($oEmp->totalDelay > 15)
                                        color: red;
                                    @endif                                        
                                ">
                                    <b>{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oEmp->totalDelay) }}</b>
                                </td>
                                <td style="text-align: right; padding-left: 8px; padding-right: 8px;">
                                    <b>{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oEmp->totalAditional) }}</b>
                                </td>
                            </tr>
                            <?php
                                $i++;
                            ?>
                        </tbody>
                    </table>
                    @foreach ($oEmp->aIncidents as $oResume)
                        @if (! isset($oResume->counter) || $oResume->counter == 0)
                            @continue
                        @endif
                        <label for="">{{ isset($oResume->text) ? $oResume->text : "" }}</label>
                        <label for="">{{ isset($oResume->counter) ? $oResume->counter : "" }}</label>
                        <label for="">{{ isset($oResume->unit) ? $oResume->unit : "" }}</label>
                    @endforeach
                    
                    @if ($oEmp->totalDelay > 15)
                        <p style="color: red;">Nota: Únicamente se permiten 15 minutos acumulados de retardo en una quincena.</p>
                    @endif
                                      
                    <hr align="left" width="55%" >                       
            @endforeach
        @endif
    </div>
</body>

</html>