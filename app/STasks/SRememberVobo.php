<?php
namespace App\STasks;
use Carbon\Carbon;
use App\SUtils\SDateUtils;
use App\Models\User;
use App\Mail\rememberVoboMail;
use App\SUtils\SDateFormatUtils;
use App\Http\Controllers\PrepayrollReportController;

class SRememberVobo
{
    public static function rememberVobo()
    {
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $oToday = Carbon::today();
        $today = Carbon::now()->toDateString();
        $weekId = \DB::table('way_pay')->where('name', 'Semana')->first()->id;
        $biWeekId = \DB::table('way_pay')->where('name', 'Quincena')->first()->id;

        $arrNumberWeek = SDateUtils::getNumberOfDate($today, $weekId);
        $arrNumberBiWeek = SDateUtils::getNumberOfDate($today, $biWeekId);

        $arrDatesWeek = SDateUtils::getDatesOfPayrollNumber($arrNumberWeek[0], $arrNumberWeek[1], $weekId);
        $arrDatesBiWeek = SDateUtils::getDatesOfPayrollNumber($arrNumberBiWeek[0], $arrNumberBiWeek[1], $biWeekId);

        $lUsersWeek = \DB::table('prepayroll_report_auth_controls')
            ->where('is_week', 1)
            ->where('num_week', $arrNumberWeek[0])
            ->where('year', $arrNumberWeek[1])
            ->where('is_delete', 0)
            ->where('is_vobo', 0)
            ->where('is_rejected', 0)
            ->get()
            ->pluck('user_vobo_id');

        $lUsersBiWeek = \DB::table('prepayroll_report_auth_controls')
            ->where('is_biweek', 1)
            ->where('num_biweek', $arrNumberBiWeek[0])
            ->where('year', $arrNumberBiWeek[1])
            ->where('is_delete', 0)
            ->where('is_vobo', 0)
            ->where('is_rejected', 0)
            ->get()
            ->pluck('user_vobo_id');

        $oInitDateWeek = Carbon::parse($arrDatesWeek[0]);
        $oEndDateWeek = Carbon::parse($arrDatesWeek[1]);
        $oInitDateBiWeek = Carbon::parse($arrDatesBiWeek[0]);
        $oEndDateBiWeek = Carbon::parse($arrDatesBiWeek[1]);

        $withNotificationToWeekVobo = $config->withNotificationToWeekVobo;
        $withNotificationToBiWeekVobo = $config->withNotificationToBiWeekVobo;

        if ($withNotificationToWeekVobo) {
            $notifyDaysToCloseWeekVobo = $config->notifyDaysToCloseWeekVobo;
            foreach ($notifyDaysToCloseWeekVobo as $value) {
                $oNotifyDateWeek = $oEndDateWeek->copy()->subDays($value);

                if ($oToday->equalTo($oNotifyDateWeek) && $lUsersWeek->count() > 0) {
                    $lUsersWeek->each(function ($user) use ($oEndDateWeek, $arrNumberWeek, $value) {
                        $oUser = User::find($user);
                        // \Mail::to($oUser->email)->send(new rememberVoboMail('semanal', $oEndDateWeek->toDateString()));
                        $sDate = SDateFormatUtils::formatDate($oEndDateWeek->toDateString(), 'ddd D-m-Y');
                        \Mail::to('adrian.aviles@swaplicado.com.mx')->send(new rememberVoboMail('semanal', $sDate, 'preClose', $arrNumberWeek[0], $value));
                    });
                }
            }
        }

        if ($withNotificationToBiWeekVobo) {
            $notifyDaysToCloseBiWeekVobo = $config->notifyDaysToCloseBiWeekVobo;

            foreach ($notifyDaysToCloseBiWeekVobo as $value) {
                $oNotifyDateBiWeek = $oEndDateBiWeek->copy()->subDays($value);

                if ($oToday->equalTo($oNotifyDateBiWeek) && $lUsersBiWeek->count() > 0) {
                    $lUsersBiWeek->each(function ($user) use ($oEndDateBiWeek, $arrNumberBiWeek, $value) {
                        $oUser = User::find($user);
                        // \Mail::to($oUser->email)->send(new rememberVoboMail('quincenal', $oEndDateBiWeek->toDateString()));
                        $sDate = SDateFormatUtils::formatDate($oEndDateBiWeek->toDateString(), 'ddd D-m-Y');
                        \Mail::to('adrian.aviles@swaplicado.com.mx')->send(new rememberVoboMail('quincenal', $sDate, 'preClose', $arrNumberBiWeek[0], $value));
                    });
                }
            }
        }

        $numLastWeekCut = [];
        $numLastBiWeekCut = [];
        if ($arrNumberWeek[0] > 1) {
            $arrDatesLastWeek = SDateUtils::getDatesOfPayrollNumber($arrNumberWeek[0] - 1, $arrNumberWeek[1], $weekId);
            $arrDatesLastBiWeek = SDateUtils::getDatesOfPayrollNumber($arrNumberBiWeek[0] - 1, $arrNumberBiWeek[1], $biWeekId);

        } else if ($arrNumberWeek[0] == 1) {
            $year = $arrNumberWeek[1] - 1;
            $oWeekCut = \DB::table('week_cut')->where('year', $year)->get()->max();
            $oBiWeekCut = \DB::table('hrs_prepay_cut')->where('year', $year)->where('is_delete', 0)->get()->max();
            
            $arrDatesLastWeek = SDateUtils::getDatesOfPayrollNumber($oWeekCut->year, $oWeekCut->num, $weekId);
            $arrDatesLastBiWeek = SDateUtils::getDatesOfPayrollNumber($oBiWeekCut->year, $oBiWeekCut->num, $biWeekId);
        }
        $numLastWeekCut = SDateUtils::getNumberOfDate($arrDatesLastWeek[1], $weekId);
        $numLastBiWeekCut = SDateUtils::getNumberOfDate($arrDatesLastBiWeek[1], $biWeekId);

        $lastWeekCutIsClosed = PrepayrollReportController::prepayrollIsClosed($arrDatesLastWeek[0], $arrDatesLastWeek[1], $weekId);
        $lastBiWeekCutIsClosed = PrepayrollReportController::prepayrollIsClosed($arrDatesLastBiWeek[0], $arrDatesLastBiWeek[1], $biWeekId);

        $lastWeekCut = Carbon::parse($arrDatesLastWeek[1]);
        $lastBiWeekCut = Carbon::parse($arrDatesLastBiWeek[1]);
        $daysToCloseWeekVobo = $config->daysToCloseWeekVobo;
        $daysToCloseBiWeekVobo = $config->daysToCloseBiWeekVobo;

        $oCloseDateLastWeek = $lastWeekCut->copy()->addDays($daysToCloseWeekVobo);
        $oCloseDateLastBiWeek = $lastBiWeekCut->copy()->addDays($daysToCloseBiWeekVobo);

        $lUsersLastWeek = \DB::table('prepayroll_report_auth_controls')
            ->where('is_week', 1)
            ->where('num_week', $numLastWeekCut[0])
            ->where('year', $numLastWeekCut[1])
            ->where('is_delete', 0)
            ->where('is_vobo', 0)
            ->where('is_rejected', 0)
            ->get()
            ->pluck('user_vobo_id');

        $lUsersLastBiWeek = \DB::table('prepayroll_report_auth_controls')
            ->where('is_biweek', 1)
            ->where('num_biweek', $numLastBiWeekCut[0])
            ->where('year', $numLastBiWeekCut[1])
            ->where('is_delete', 0)
            ->where('is_vobo', 0)
            ->where('is_rejected', 0)
            ->get()
            ->pluck('user_vobo_id');

        if (!$lastWeekCutIsClosed && $daysToCloseWeekVobo != 0) {
            if ($withNotificationToWeekVobo) {
                $diffInDays = $oToday->diffInDays($lastWeekCut);
                if ($diffInDays % $daysToCloseWeekVobo == 0) {
                    if ($oToday->gte($lastWeekCut) && $lUsersLastWeek->count() > 0) {
                        $lUsersLastWeek->each(function ($user) use ($lastWeekCut, $numLastWeekCut, $diffInDays) {
                            $oUser = User::find($user);
                            // \Mail::to($oUser->email)->send(new rememberVoboMail('semanal', $lastWeekCut->toDateString()));
                            $sDate = SDateFormatUtils::formatDate($lastWeekCut->toDateString(), 'ddd D-m-Y');
                            \Mail::to('adrian.aviles@swaplicado.com.mx')->send(new rememberVoboMail('semanal', $sDate, 'afterClose', $numLastWeekCut[0], $diffInDays));
                        });
                    }
                }
            }
        }

        if (!$lastBiWeekCutIsClosed && $daysToCloseBiWeekVobo != 0) {
            if ($withNotificationToBiWeekVobo) {
                $diffInDays = $oToday->diffInDays($lastBiWeekCut);
                if ($diffInDays % $daysToCloseBiWeekVobo == 0) {
                    if ($oToday->gte($lastBiWeekCut) && $lUsersLastBiWeek->count() > 0) {
                        $lUsersLastBiWeek->each(function ($user) use ($lastBiWeekCut, $numLastWeekCut, $diffInDays) {
                            $oUser = User::find($user);
                            // \Mail::to($oUser->email)->send(new rememberVoboMail('quincenal', $lastBiWeekCut->toDateString()));
                            $sDate = SDateFormatUtils::formatDate($lastBiWeekCut->toDateString(), 'ddd D-m-Y');
                            \Mail::to('adrian.aviles@swaplicado.com.mx')->send(new rememberVoboMail('quincenal', $sDate, 'afterClose', $numLastWeekCut[0], $diffInDays));
                        });
                    }
                }
            }
        }

    }
}