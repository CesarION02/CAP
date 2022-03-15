<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\PrepayReportConfig;
use App\Models\PrepayReportControl;
use App\Models\employees;
use App\SUtils\SDateUtils;
use App\SUtils\SPrepayrollUtils;

class PrepayrollReportController extends Controller
{
    public function cfgVobos(Request $request)
    {
        $configurations = \DB::table('prepayroll_report_configs AS prc')
                                ->leftJoin('users AS u', 'prc.user_n_id', '=', 'u.id')
                                ->get();

        return view('prepayrollcontrol.voboscfg', [
            'configurations' => $configurations
        ]);
    }

    public function create(Request $request)
    {
        $users = \DB::table('users')
                        ->select(['id', 'name'])
                        ->where('is_delete', 0)
                        ->get();

        return view('prepayrollcontrol.voboscfgcreate', [
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $oCfg = new PrepayReportConfig();

        $oCfg->since_date = $request->since_date;
        $oCfg->is_week = $request->type_pay == 1;
        $oCfg->is_biweek = $request->type_pay == 2;
        $oCfg->is_required = isset($request->is_required);
        $oCfg->order_vobo = $request->order_vobo;
        $oCfg->rol_n_name = $request->rol_n_name;
        $oCfg->user_n_id = $request->user_n_id;
        $oCfg->is_delete = 0;
        $oCfg->created_by = \Auth::user()->id;
        $oCfg->updated_by = \Auth::user()->id;

        $oCfg->save();

        return redirect()->route('cfg_vobos');
    }

    public function edit($id)
    {
        $oCfg = PrepayReportConfig::find($id);

        $users = \DB::table('users')
                        ->select(['id', 'name'])
                        ->where('is_delete', 0)
                        ->get();
    
        return view('prepayrollcontrol.voboscfgedit', [
            'oCfg' => $oCfg,
            'users' => $users
        ]);
    }

    public function update(Request $request)
    {
        $oCfg = PrepayReportConfig::find($request->id_configuration);

        $oCfg->since_date = $request->since_date;
        $oCfg->is_week = $request->type_pay == 1;
        $oCfg->is_biweek = $request->type_pay == 2;
        $oCfg->is_required = isset($request->is_required);
        $oCfg->order_vobo = $request->order_vobo;
        $oCfg->rol_n_name = $request->rol_n_name;
        $oCfg->user_n_id = $request->user_n_id;
        $oCfg->updated_by = \Auth::user()->id;

        $oCfg->save();

        return redirect()->route('cfg_vobos');
    }
    
    public static function prepayrollReportVobos($sStartDate, $sEndDate)
    {
        $oSDate = Carbon::parse($sStartDate);
        $oEDate = Carbon::parse($sEndDate);

        /**
         * Semana
         */
        $number = PrepayrollReportController::process($sStartDate, \SCons::PAY_W_S, 0);
        if ($number > 0) {
            PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_S, $number, $oSDate->year);
        }

        $number = PrepayrollReportController::process($sEndDate, \SCons::PAY_W_S, 0);
        if ($number > 0) {
            PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_S, $number, $oEDate->year);
        }

        $oDate = (clone $oSDate)->addWeek();
        while ($oDate->lessThan($oEDate)) {
            $number = PrepayrollReportController::process($oDate->toDateString(), \SCons::PAY_W_S, 0);
            if ($number > 0) {
                PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_S, $number, $oDate->year);
            }
            $number = PrepayrollReportController::process($oDate->toDateString(), \SCons::PAY_W_Q, 0);
            if ($number > 0) {
                PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_Q, $number, $oDate->year);
            }
            $oDate->addWeek();
        }

        /**
         * Quincena
         */
        $number = PrepayrollReportController::process($sStartDate, \SCons::PAY_W_Q, 0);
        if ($number > 0) {
            PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_Q, $number, $oSDate->year);
        }

        $number = PrepayrollReportController::process($sEndDate, \SCons::PAY_W_Q, 0);
        if ($number > 0) {
            PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_Q, $number, $oEDate->year);
        }
    }

    /**
     * Si retorna un valor mayor que 0 es que se deben crear los registros de autorización
     *
     * @param string $dtDate [yyy-mm-yy]
     * @param int $payTypeId \SCons::PAY_W_Q | \SCons::PAY_W_S
     * @param int $idEmployee
     * @return void
     */
    public static function process($dtDate, $payTypeId, $idEmployee)
    {
        if ($payTypeId == 0) {
            $oEmployee = employees::findOrFail($idEmployee);
            $payTypeId = $oEmployee->pay_type_id;
        }

        $number = SDateUtils::getNumberOfDate($dtDate, $payTypeId);

        $oDate = Carbon::parse($dtDate);
        
        //Si aún no hay vobos, se crean los registros de autorización
        return $number;
    }

    /**
     * Guarda los registros de autorización
     *
     * @param int $payTypeId \SCons::PAY_W_Q | \SCons::PAY_W_S
     * @param int $number
     * @return void
     */
    public static function storePrepayReportControls($payTypeId, $number, $year)
    {
        $cfgs = \DB::table('prepayroll_report_configs AS prc')
                    ->where('is_delete', false);

        $dates = SDateUtils::getDatesOfPayrollNumber($number, $year, $payTypeId);
        if (count($dates) > 0) {
            $cfgs = $cfgs->where('since_date', '<=', $dates[0]);
        }

        if ($payTypeId == \SCons::PAY_W_Q) {
            $cfgs = $cfgs->where('is_biweek', true);
        }
        else {
            $cfgs = $cfgs->where('is_week', true);
        }

        $cfgs = $cfgs->orderBy('order_vobo', 'ASC')
                    ->get();

         /**
         * Consultar si ya hay vobos para la semana o quincena recibida
         */
        $lVobos = \DB::table('prepayroll_report_auth_controls AS prac')
                            ->where('year', $year);

        try {
            \DB::beginTransaction();

            $orderBovo = 1;
            foreach ($cfgs as $cfg) {
                if ($cfg->user_n_id > 0) {
                    $prac = new PrepayReportControl();
                    
                    $lVobo = clone $lVobos;
                    $lVobo = $lVobo->where('user_vobo_id', $cfg->user_n_id);

                    if ($payTypeId == \SCons::PAY_W_Q) {
                        $prac->is_biweek = true;
                        $prac->num_biweek = $number;
                        $lVobo = $lVobo->where('num_biweek', $number);
                    }
                    else {
                        $prac->is_week = true;
                        $prac->num_week = $number;
                        $lVobo = $lVobo->where('num_week', $number);
                    }

                    $oVobo = $lVobo->first();

                    if ($oVobo != null) continue;
                    
                    $prac->year = $year;
                    $prac->is_required = $cfg->is_required;
                    $prac->is_vobo = false;
                    $prac->dt_vobo = null;
                    $prac->is_rejected = false;
                    $prac->dt_rejected = null;
                    $prac->order_vobo = $orderBovo++;
                    $prac->is_delete = false;
                    $prac->user_vobo_id = $cfg->user_n_id;
                    $prac->created_by = \Auth::user()->id;
                    $prac->updated_by = \Auth::user()->id;
    
                    $prac->save();
                }
                elseif ($cfg->rol_n_name != null) {
                    $lUsers = App\SUtils\SPermissions::usersWithRol('Supervisores');
    
                    foreach ($lUsers as $user) {
                        $prac = new PrepayReportControl();
        
                        if ($payTypeId == \SCons::PAY_W_Q) {
                            $prac->is_biweek = true;
                            $prac->num_biweek = $number;
                        }
                        else {
                            $prac->is_week = true;
                            $prac->num_week = $number;
                        }
                        
                        $prac->year = $year;
                        $prac->is_required = $cfg->is_required;
                        $prac->is_vobo = false;
                        $prac->dt_vobo = null;
                        $prac->is_rejected = false;
                        $prac->dt_rejected = null;
                        $prac->order_vobo = $orderBovo;
                        $prac->is_delete = false;
                        $prac->user_vobo_id = $user->id;
                        $prac->created_by = \Auth::user()->id;
                        $prac->updated_by = \Auth::user()->id;
    
                        $prac->save();
                    }

                    $orderBovo++;
                }
            }

            \DB::commit();
        }
        catch (\Throwable $th) {
            \DB::rollBack();
        }
    }

    /**
     * Determina si la prenómina no tiene pendientes vistos buenos
     *
     * @param [type] $dtDate
     * @param [type] $payTypeId
     * @return boolean
     */
    public static function canMakeAdjust($dtDate, $payTypeId)
    {
        $number = SDateUtils::getNumberOfDate($dtDate, $payTypeId);
        $oDate = Carbon::parse($dtDate);

        $lVobos = \DB::table('prepayroll_report_auth_controls AS prac');

        if ($payTypeId == \SCons::PAY_W_Q) {
            $lVobos = $lVobos->where('is_biweek', true)
                            ->where('num_biweek', $number);
        }
        else {
            $lVobos = $lVobos->where('is_week', true)
                            ->where('num_week', $number);
        }

        $lVobos = $lVobos->where('is_delete', false)
                            ->where('year', $oDate->year)
                            ->orderBy('order_vobo', 'ASC');

        $lVobosEmpty = clone $lVobos;

        /**
         * Si no hay vobos, se retorna true
         */
        $lVobosEmpty = $lVobosEmpty->get();
        if (count($lVobosEmpty) == 0) {
            return true;
        }

        $lVoboUsr = clone $lVobos;
        $oVoboUsr = $lVoboUsr->where('user_vobo_id', \Auth::user()->id)->first();

        if ($oVoboUsr != null) {
            $lVobosMaj = clone $lVobos;
            $lVobosMaj = $lVobosMaj->where('order_vobo', '>', $oVoboUsr->order_vobo)
                            ->where('is_vobo', true)
                            ->where('user_vobo_id', '!=', \Auth::user()->id);

            $userGroups = SPrepayrollUtils::getUserGroups(\Auth::user()->id);

            $lUsersBosses = [];
            foreach ($userGroups as $group) {
                $aux = [];
                $aux[] = $group;
                $lGroups = SPrepayrollUtils::getAncestryOfGroups($aux);

                $users = \DB::table('prepayroll_groups AS pg')
                            ->whereIn('id_group', $lGroups)
                            ->pluck('head_user_id')
                            ->toArray();

                $lUsersBosses = array_merge($lUsersBosses, $users);
            }

            $lVobosMaj = $lVobosMaj->whereIn('user_vobo_id', $lUsersBosses)
                                    ->get();
            
            if (count($lVobosMaj) > 0) {
                return false;
            }
        }

        $myVoBo = clone $lVobos;
        $myVoBo = $myVoBo->where('is_vobo', true)
                                    ->where('user_vobo_id', \Auth::user()->id)
                                    ->get();

        if (count($myVoBo) > 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Determina si la prenómina no tiene pendientes vistos buenos
     *
     * @param [type] $dtDate
     * @param [type] $payTypeId
     * @return boolean
     */
    public static function isFreeVobo($dtDate, $payTypeId)
    {
        $number = SDateUtils::getNumberOfDate($dtDate, $payTypeId);
        $oDate = Carbon::parse($dtDate);

        $lVobos = \DB::table('prepayroll_report_auth_controls AS prac');

        if ($payTypeId == \SCons::PAY_W_Q) {
            $lVobos = $lVobos->where('is_biweek', true)
                            ->where('num_biweek', $number);
        }
        else {
            $lVobos = $lVobos->where('is_week', true)
                            ->where('num_week', $number);
        }

        $lVobos = $lVobos->where('is_delete', false)
                            ->where('year', $oDate->year)
                            ->orderBy('order_vobo', 'ASC');

        $lVobosEmpty = clone $lVobos;

        /**
         * Si no hay vobos, se retorna true
         */
        $lVobosEmpty = $lVobosEmpty->get();
        if (count($lVobosEmpty) == 0) {
            return true;
        }

        $lVoboUsr = clone $lVobos;
        $oVoboUsr = $lVoboUsr->where('user_vobo_id', \Auth::user()->id)->first();

        $lVobosMaj = clone $lVobos;
        if ($oVoboUsr != null) {
            $lVobosMaj = $lVobosMaj->where('order_vobo', '>', $oVoboUsr->order_vobo)
                            ->where('is_vobo', true)
                            ->where('user_vobo_id', '!=', \Auth::user()->id)
                            ->get();
            
            if (count($lVobosMaj) > 0) {
                return false;
            }

            return true;
        }

        $lVobosSome = clone $lVobos;
        $lVobosSome = $lVobosSome->where('is_vobo', true)
                                ->get();

        if (count($lVobosSome) > 0) {
            return false;
        }
        
        return true;
    }

    public static function isFreeVoboPrepayroll($dtDate, $payTypeId)
    {
        $number = SDateUtils::getNumberOfDate($dtDate, $payTypeId);
        $oDate = Carbon::parse($dtDate);

        $lVobos = \DB::table('prepayroll_report_auth_controls AS prac');

        if ($payTypeId == \SCons::PAY_W_Q) {
            $lVobos = $lVobos->where('is_biweek', true)
                            ->where('num_biweek', $number);
        }
        else {
            $lVobos = $lVobos->where('is_week', true)
                            ->where('num_week', $number);
        }

        $lVobos = $lVobos->where('is_delete', false)
                            ->where('year', $oDate->year)
                            ->orderBy('order_vobo', 'ASC');

        $lVobosEmpty = clone $lVobos;

        /**
         * Si no hay vobos, se retorna true
         */
        $lVobosEmpty = $lVobosEmpty->get();
        if (count($lVobosEmpty) == 0) {
            return true;
        }

        $lVobosReq = clone $lVobos;
        $lVobosReq = $lVobosReq->where('is_vobo', false)
                                ->where('is_required', true)
                                ->get();
            
        if (count($lVobosReq) > 0) {
            return false;
        }
        
        return true;
    }
}
