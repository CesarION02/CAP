<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAP Notificación</title>
</head>

{{-- <body style="font-family: 'Courier New', monospace"> --}}
<body style="font-family: 'Georgia, serif'">
    <div>
        <h1>Reporte de Jornadas Laborales <b>({{ $typePay }})</b></h1>
        <h2>{{ "Periodo: ".$startDate." - ".$endDate."" }}</h2>

        @if (count($lData) == 0)
            <h3>No hay información qué mostrar.</h3>
        @else
            <?php
                $i = 1;
            ?>
            @foreach ($lData as $oEmp)
                    <h4><b>{{ $oEmp->numEmployee." - ".$oEmp->employee }}</b></h4>
                    <h5>{{ "Departamento: ".strtoupper($oEmp->departmentName) }}</h5>

                    <table>
                        <thead>
                            <tr>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>T. trabajado</th>
                                <th>T. retardo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($oEmp->lRows as $oRow)
                            <tr>
                                <td 
                                @if ($i % 2 == 0)
                                    style="background-color: rgb(192, 192, 192)"    
                                @endif
                                >
                                    {{ strlen($oRow->inDateTime) > 11 ? \Carbon\Carbon::parse($oRow->inDateTime)->format('d-m-Y H:i:s') : \Carbon\Carbon::parse($oRow->outDateTime)->format('d-m-Y') }}
                                </td>
                                <td 
                                @if ($i % 2 == 0)
                                    style="background-color: rgb(192, 192, 192)"    
                                @endif
                                >
                                    {{ strlen($oRow->outDateTime) > 11 ? \Carbon\Carbon::parse($oRow->outDateTime)->format('d-m-Y H:i:s') : \Carbon\Carbon::parse($oRow->outDateTime)->format('d-m-Y') }}
                                </td>
                                <td style="text-align: right;
                                @if ($i % 2 == 0)
                                    background-color: rgb(192, 192, 192)    
                                @endif
                                ">
                                    {{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oRow->workedTime) }}
                                </td>
                                <td style="text-align: right;
                                @if ($i % 2 == 0)
                                    background-color: rgb(192, 192, 192)    
                                @endif
                                ">
                                    {{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oRow->entryDelayMinutes) }}
                                </td>
                            </tr>
                            <?php
                                $i++;
                            ?>
                            @endforeach
                            <tr>
                                <td><b>TOTAL RETARDO</b></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: right;""><b>{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oEmp->totalDelay) }}</b></td>
                            </tr>
                            <?php
                                $i++;
                            ?>
                        </tbody>
                    </table>
                    <hr align="left" width="55%" >
            @endforeach
        @endif
    </div>
</body>

</html>