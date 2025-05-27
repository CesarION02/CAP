<?php
namespace App\STasks;
use Carbon\Carbon;
use App\SUtils\SDateUtils;
use App\Models\User;
use App\Mail\rememberVoboMail;
use App\SUtils\SDateFormatUtils;
use App\Http\Controllers\PrepayrollReportController;
use App\SData\SDataProcess;
use App\SUtils\SGenUtils;
use App\Mail\rememberEmployeeNoCheckMail;

class SRememberEmployeeNoCheck
{
    public static function rememberCheckEmployee()
    {
        try {
            \App\Http\Controllers\SyncController::toSyncronize();
            
            $config = \App\SUtils\SConfiguration::getConfigurations();

            $syncDate = Carbon::parse($config->lastSyncDateTime);
            $dayToCheck = Carbon::now()->subDay();

            if ( $syncDate->lte($dayToCheck) ) {
                \Log::error('Error en rememberCheckEmployee, la fecha de sincronizacion es inferior a la fecha de comparacion, dayToCheck: ' . 
                        $dayToCheck->toDateString() . ', syncDate: ' . $syncDate->toDateString());
                return;
            }

            $sDate = Carbon::today()->subDay()->toDateString();
            $weekId = \DB::table('way_pay')->where('name', 'Semana')->first()->id;
            $biWeekId = \DB::table('way_pay')->where('name', 'Quincena')->first()->id;

            $rememberCheckEmployeeWeek = $config->rememberCheckEmployeeWeek;
            $rememberCheckEmployeeBiWeek = $config->rememberCheckEmployeeBiWeek;

            if ($rememberCheckEmployeeBiWeek) {
                $lEmployeesBiWeek = SGenUtils::toEmployeeIds($biWeekId, 0, [], [], 0);
                $lEmployeesBiWeek = $lEmployeesBiWeek->filter(function ($item) {
                    return $item->ben_pol_id == 1;
                });

                foreach ($lEmployeesBiWeek as $emp) {
                    try {
                        $type = 0;
                        $oEmp = SGenUtils::toEmployeeIds($biWeekId, 0, [], [$emp->id], 0);
                        $lRowsBiWeek = SDataProcess::process($sDate, $sDate, $biWeekId, $oEmp);

                        $timeIn = "";
                        $lFlRowsIn = $lRowsBiWeek->filter(function ($item) use ($sDate) {
                            return Carbon::parse($item->inDateTime)->isSameDay($sDate);
                        });
                        if (count($lFlRowsIn) > 0) {
                            $oRow = $lFlRowsIn->first();
                            if (strpos($oRow->comments, 'Sin entrada') === false) {
                                $timeIn = Carbon::parse($oRow->inDateTime)->toTimeString();
                            }
                        }

                        $timeOut = "";
                        $lFlRowsOut = $lRowsBiWeek->filter(function ($item) use ($sDate) {
                            return Carbon::parse($item->inDateTime)->isSameDay($sDate);
                        });
                        if (count($lFlRowsOut) > 0) {
                            $oRow = $lFlRowsOut->first();
                            if (strpos($oRow->comments, 'Sin salida') === false) {
                                $timeOut = Carbon::parse($oRow->outDateTime)->toTimeString();
                            }
                        }

                        $time = "";
                        $lFlRowsOut = $lRowsBiWeek->filter(function ($item) use ($sDate) {
                            return Carbon::parse($item->inDateTime)->isSameDay($sDate);
                        });
                        if (count($lFlRowsOut) > 0) {
                            $oRow = $lFlRowsOut->first();
                            if (strpos($oRow->comments, 'Sin checadas') === false) {
                                $time = Carbon::parse($oRow->outDateTime)->toTimeString();
                            }
                        }

                        if (strlen($timeIn) == 0) {
                            $type = 1;
                        }
                        if (strlen($timeOut) == 0) {
                            $type = 2;
                        }
                        if (strlen($time) == 0) {
                            $type = 3;
                        }

                        if ($type != 0) {
                            $oUser = \DB::connection('mysqlGlobalUsers')
                                ->table('global_users')
                                ->where('external_id', $emp->external_id)
                                ->where('is_active', 1)
                                ->where('is_deleted', 0)
                                ->select('email')
                                ->first();

                            \Mail::to($oUser->email)->send(new rememberEmployeeNoCheckMail($type, SDateFormatUtils::formatDate($sDate, 'ddd D-m-Y')));

                            $log = 'Tipo: ' . 
                                    ($type == 1 ? 'entrada' : 
                                        ($type == 2 ? 'salida' : 
                                            ($type == 3 ? 'falta' : $type))) . 
                                            ', fecha: ' . SDateFormatUtils::formatDate($sDate, 'ddd D-m-Y') .
                                            ', empleado: ' . $emp->name .
                                            ', correo: ' . $oUser->email;

                            \Log::channel('rememberEmployeeCheck_log')
                                ->info($log);
                        }
                    } catch (\Throwable $th) {
                        \Log::error('Error en recordatorio de no checada');
                        \Log::error($th);
                    }
                }
            }

            if ($rememberCheckEmployeeWeek) {
                $lEmployeesWeek = SGenUtils::toEmployeeIds($weekId, 0, [], [], 0);
                $lEmployeesWeek = $lEmployeesWeek->filter(function ($item) {
                    return $item->ben_pol_id == 1;
                });

                foreach ($lEmployeesWeek as $emp) {
                    try {
                        $type = 0;
                        $oEmp = SGenUtils::toEmployeeIds($weekId, 0, [], [$emp->id], 0);
                        $lRowsWeek = SDataProcess::process($sDate, $sDate, $weekId, $oEmp);

                        $row = $lRowsWeek->first();
                        if ($row->scheduleText == "Sin horario" || $row->scheduleText == "") {
                            continue;
                        }

                        $timeIn = "";
                        $scheduleIn = "";
                        $lFlRowsIn = $lRowsWeek->filter(function ($item) use ($sDate) {
                            return Carbon::parse($item->outDateTime)->isSameDay($sDate);
                        });
                        if (count($lFlRowsIn) > 0) {
                            $oRow = $lFlRowsIn->first();
                            $scheduleIn = $oRow->inDateTimeSch;
                            if (strpos($oRow->comments, 'Sin entrada') === false) {
                                $timeIn = Carbon::parse($oRow->inDateTime)->toTimeString();
                            }
                        }

                        $timeOut = "";
                        $scheduleOut = "";
                        $lFlRowsOut = $lRowsWeek->filter(function ($item) use ($sDate) {
                            return Carbon::parse($item->outDateTime)->isSameDay($sDate);
                        });
                        if (count($lFlRowsOut) > 0) {
                            $oRow = $lFlRowsOut->first();
                            $scheduleOut = $oRow->outDateTimeSch;
                            if (strpos($oRow->comments, 'Sin salida') === false) {
                                $timeOut = Carbon::parse($oRow->outDateTime)->toTimeString();
                            }
                        }

                        $time = "";
                        $lFlRowsOut = $lRowsWeek->filter(function ($item) use ($sDate) {
                            return Carbon::parse($item->outDateTime)->isSameDay($sDate);
                        });
                        if (count($lFlRowsOut) > 0) {
                            $oRow = $lFlRowsOut->first();
                            $scheduleIn = $oRow->inDateTimeSch;
                            $scheduleOut = $oRow->outDateTimeSch;
                            if (strpos($oRow->comments, 'Sin checadas') === false) {
                                $time = Carbon::parse($oRow->outDateTime)->toTimeString();
                            }
                        }

                        if (strlen($timeIn) == 0) {
                            $type = 1;
                        }
                        if (strlen($timeOut) == 0) {
                            $type = 2;
                        }
                        if (strlen($time) == 0) {
                            $type = 3;
                        }

                        if ($type != 0) {
                            $nextUser = false;
                            switch ($type) {
                                case 1:
                                    if ($scheduleIn != "" && $scheduleIn != null) {
                                        $oScheduleIn = Carbon::parse($scheduleIn);
                                        if ($oScheduleIn->gt($dayToCheck)) {
                                            $nextUser = true;
                                        }
                                    } else {
                                        $nextUser = true;
                                    }

                                    break;
                                case 2:
                                    if ($scheduleOut != "" && $scheduleOut != null) {
                                        $oScheduleOut = Carbon::parse($scheduleOut);
                                        if ($oScheduleOut->gt($dayToCheck)) {
                                            $nextUser = true;
                                        }
                                    } else {
                                        $nextUser = true;
                                    }
                                    break;
                                case 3:
                                    if ($scheduleIn != "" && $scheduleIn != null && $scheduleOut != "" && $scheduleOut != null) {
                                        $oScheduleIn = Carbon::parse($scheduleIn);
                                        $oScheduleOut = Carbon::parse($scheduleOut);
                                        if ($oScheduleIn->gt($dayToCheck) || $oScheduleOut->gt($dayToCheck)) {
                                            $nextUser = true;
                                        }
                                    } else {
                                        $nextUser = true;
                                    }
                                    break;
                                default:
                                    # code...
                                    break;
                            }

                            if ($nextUser) {
                                continue;
                            }

                            $oUser = \DB::connection('mysqlGlobalUsers')
                                ->table('global_users')
                                ->where('external_id', $emp->external_id)
                                ->where('is_active', 1)
                                ->where('is_deleted', 0)
                                ->select('email')
                                ->first();

                            \Mail::to($oUser->email)->send(new rememberEmployeeNoCheckMail($type, SDateFormatUtils::formatDate($sDate, 'ddd D-m-Y')));
                        
                            $log = 'Tipo: ' . 
                                    ($type == 1 ? 'entrada' : 
                                        ($type == 2 ? 'salida' : 
                                            ($type == 3 ? 'falta' : $type))) . 
                                            ', fecha: ' . SDateFormatUtils::formatDate($sDate, 'ddd D-m-Y') .
                                            ', empleado: ' . $emp->name .
                                            ', correo: ' . $oUser->email;

                            \Log::channel('rememberEmployeeCheck_log')
                                ->info($log);
                        }
                    } catch (\Throwable $th) {
                        \Log::error('Error en recordatorio de no checada');
                        \Log::error($th);
                    }
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
        }
    }
}