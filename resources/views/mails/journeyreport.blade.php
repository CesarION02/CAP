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
        <h1>Reporte de entradas y salidas
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
                        <thead>
                            @if (is_null($aColumns))
                                <tr>
                                    <th>Fecha E.</th>
                                    <th>Entrada</th>
                                    <th>Fecha S.</th>
                                    <th>Salida</th>
                                    <th>T. trabajado</th>
                                    <th>T. retardo</th>
                                    <th>Incidencias</th>
                                </tr>
                            @else
                                <tr>
                                    @foreach ($aColumns as $columElem)
                                        @switch($columElem)
                                            @case("date_in")
                                                <th>Fecha E.</th>
                                                @break
                                            @case("time_in")
                                                <th>Entrada</th>
                                                @break
                                            @case("date_out")
                                                <th>Fecha S.</th>
                                                @break
                                            @case("time_out")
                                                <th>Salida</th>
                                                @break
                                            @case("worked")
                                                <th>T. trabajado</th>
                                                @break
                                            @case("delay")
                                                <th>T. retardo</th>
                                                @break
                                            @case("events")
                                                <th>Incidencias</th>
                                                @break
                                            @default
                                                @break
                                        @endswitch
                                    @endforeach
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @foreach ($oEmp->lRows as $oRow)
                            @if (is_null($aColumns))
                                <tr>
                                    <td style="padding-left: 8px; padding-right: 8px; 
                                        @if ($i % 2 == 0)
                                            background-color: rgb(217, 217, 217)
                                        @endif
                                    ">
                                        {{ \Carbon\Carbon::parse($oRow->inDateTime)->format('d-m-Y') }}
                                    </td>
                                    <td style="padding-left: 8px; padding-right: 8px; 
                                        @if ($i % 2 == 0)
                                            background-color: rgb(217, 217, 217)
                                        @endif
                                    ">
                                        {{ strlen($oRow->inDateTime) > 11 ? (\Carbon\Carbon::parse($oRow->inDateTime)->format('H:i:s')) : "--" }}
                                    </td>
                                    <td style="padding-left: 8px; padding-right: 8px; 
                                        @if ($i % 2 == 0)
                                            background-color: rgb(217, 217, 217)
                                        @endif
                                    ">
                                        {{ \Carbon\Carbon::parse($oRow->outDateTime)->format('d-m-Y') }}
                                    </td>
                                    <td style="padding-left: 8px; padding-right: 8px; 
                                        @if ($i % 2 == 0)
                                            background-color: rgb(217, 217, 217)
                                        @endif
                                    ">
                                        {{ strlen($oRow->outDateTime) > 11 ? \Carbon\Carbon::parse($oRow->outDateTime)->format('H:i:s') : "--" }}
                                    </td>
                                    <td style="text-align: right; padding-left: 8px; padding-right: 8px; 
                                        @if ($i % 2 == 0)
                                            background-color: rgb(217, 217, 217)
                                        @endif
                                    ">
                                        {{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oRow->workedTime) }}
                                    </td>
                                    <td style="text-align: right; padding-left: 8px; padding-right: 8px; 
                                        @if ($i % 2 == 0)
                                            background-color: rgb(217, 217, 217)
                                        @endif
                                    ">
                                        {{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oRow->entryDelayMinutes) }}
                                    </td>
                                    <td style="padding-left: 8px; padding-right: 8px; 
                                        @if ($i % 2 == 0)
                                            background-color: rgb(217, 217, 217)
                                        @endif
                                    ">
                                        {{ $oRow->eventsText }}
                                    </td>
                                </tr>
                            @else
                                <tr>
                                @foreach ($aColumns as $columElem)
                                    @switch($columElem)
                                        @case("date_in")
                                            <td style="padding-left: 8px; padding-right: 8px; 
                                                @if ($i % 2 == 0)
                                                    background-color: rgb(217, 217, 217)
                                                @endif
                                            ">
                                                {{ \Carbon\Carbon::parse($oRow->inDateTime)->format('d-m-Y') }}
                                            </td>
                                            @break
                                        @case("time_in")
                                            <td style="padding-left: 8px; padding-right: 8px; 
                                                @if ($i % 2 == 0)
                                                    background-color: rgb(217, 217, 217)
                                                @endif
                                            ">
                                                {{ strlen($oRow->inDateTime) > 11 ? (\Carbon\Carbon::parse($oRow->inDateTime)->format('H:i:s')) : "--" }}
                                            </td>
                                            @break
                                        @case("date_out")
                                            <td style="padding-left: 8px; padding-right: 8px; 
                                                @if ($i % 2 == 0)
                                                    background-color: rgb(217, 217, 217)
                                                @endif
                                            ">
                                                {{ \Carbon\Carbon::parse($oRow->outDateTime)->format('d-m-Y') }}
                                            </td>
                                            @break
                                        @case("time_out")
                                            <td style="padding-left: 8px; padding-right: 8px; 
                                                @if ($i % 2 == 0)
                                                    background-color: rgb(217, 217, 217)
                                                @endif
                                            ">
                                                {{ strlen($oRow->outDateTime) > 11 ? \Carbon\Carbon::parse($oRow->outDateTime)->format('H:i:s') : "--" }}
                                            </td>
                                            @break
                                        @case("worked")
                                            <td style="text-align: right; padding-left: 8px; padding-right: 8px; 
                                                @if ($i % 2 == 0)
                                                    background-color: rgb(217, 217, 217)
                                                @endif
                                            ">
                                                {{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oRow->workedTime) }}
                                            </td>
                                            @break
                                        @case("delay")
                                            <td style="text-align: right; padding-left: 8px; padding-right: 8px; 
                                                @if ($i % 2 == 0)
                                                    background-color: rgb(217, 217, 217)
                                                @endif
                                            ">
                                                {{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oRow->entryDelayMinutes) }}
                                            </td>
                                            @break
                                        @case("events")
                                            <td style="padding-left: 8px; padding-right: 8px; 
                                                @if ($i % 2 == 0)
                                                    background-color: rgb(217, 217, 217)
                                                @endif
                                            ">
                                                {{ $oRow->eventsText }}
                                            </td>
                                            @break
                                        @default
                                            @break
                                    @endswitch
                                @endforeach
                                </tr>
                            @endif
                                <?php
                                    $i++;
                                ?>
                            @endforeach
                            @if (is_null($aColumns))
                                <tr>
                                    <td colspan="5"><b>{{ ($oEmp->numEmployee." - ".$oEmp->employee) }}</b></td>
                                    <td style="text-align: right; padding-left: 8px; padding-right: 8px; "">
                                        <b>{{ \App\SUtils\SDelayReportUtils::convertToHoursMins($oEmp->totalDelay) }}</b>
                                    </td>
                                </tr>
                            @else
                                <tr style="background-color: #acbbc9">
                                    <td colspan="6"><b>{{ ($oEmp->numEmployee." - ".$oEmp->employee) . " / Total retardo: " . \App\SUtils\SDelayReportUtils::convertToHoursMins($oEmp->totalDelay) }}</b></td>
                                </tr>
                            @endif
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