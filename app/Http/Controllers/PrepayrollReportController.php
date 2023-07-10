<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\employees;
use App\Models\PrepayrollDelegation;
use App\Models\prepayrollVoboSkipped;
use App\Models\PrepayReportConfig;
use App\Models\PrepayReportControl;
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
                        ->orderBy('name')
                        ->get();

        return view('prepayrollcontrol.voboscfgcreate', [
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $oCfg = new PrepayReportConfig();

        $oCfg->since_date = $request->since_date;
        $oCfg->until_date = $request->until_date;
        $oCfg->is_week = $request->type_pay == 1;
        $oCfg->is_biweek = $request->type_pay == 2;
        $oCfg->is_required = isset($request->is_required);
        $oCfg->order_vobo = $request->order_vobo;
        $oCfg->rol_n_name = $request->rol_n_name;
        $oCfg->user_n_id = $request->user_n_id;
        $oCfg->is_global = isset($request->is_global);
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
                        ->orderBy('name')
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
        $oCfg->until_date = $request->until_date;
        $oCfg->is_week = $request->type_pay == 1;
        $oCfg->is_biweek = $request->type_pay == 2;
        $oCfg->is_required = isset($request->is_required);
        $oCfg->order_vobo = $request->order_vobo;
        $oCfg->rol_n_name = $request->rol_n_name;
        $oCfg->user_n_id = $request->user_n_id;
        $oCfg->is_global = isset($request->is_global);
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
        if ($number[0] > 0) {
            PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_S, $number[0], $number[1]);
        }

        $number = PrepayrollReportController::process($sEndDate, \SCons::PAY_W_S, 0);
        if ($number[0] > 0) {
            PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_S, $number[0], $number[1]);
        }

        $oDate = (clone $oSDate)->addWeek();
        while ($oDate->lessThan($oEDate)) {
            $number = PrepayrollReportController::process($oDate->toDateString(), \SCons::PAY_W_S, 0);
            if ($number[0] > 0) {
                PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_S, $number[0], $number[1]);
            }
            $number = PrepayrollReportController::process($oDate->toDateString(), \SCons::PAY_W_Q, 0);
            if ($number[0] > 0) {
                PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_Q, $number[0], $number[1]);
            }
            $oDate->addWeek();
        }

        /**
         * Quincena
         */
        $number = PrepayrollReportController::process($sStartDate, \SCons::PAY_W_Q, 0);
        if ($number[0] > 0) {
            PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_Q, $number[0], $number[1]);
        }

        $number = PrepayrollReportController::process($sEndDate, \SCons::PAY_W_Q, 0);
        if ($number[0] > 0) {
            PrepayrollReportController::storePrepayReportControls(\SCons::PAY_W_Q, $number[0], $number[1]);
        }
    }

    /**
     * Si retorna un valor mayor que 0 es que se deben crear los registros de autorización
     *
     * @param string $dtDate [yyy-mm-yy]
     * @param int $payTypeId \SCons::PAY_W_Q | \SCons::PAY_W_S
     * @param int $idEmployee
     * @return array
     */
    public static function process($dtDate, $payTypeId, $idEmployee)
    {
        if ($payTypeId == 0) {
            $oEmployee = employees::findOrFail($idEmployee);
            $payTypeId = $oEmployee->pay_type_id;
        }

        $number = SDateUtils::getNumberOfDate($dtDate, $payTypeId);
        
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
            $cfgs = $cfgs->where('since_date', '<=', $dates[0])
                        ->where(function ($query) use ($dates) {
                            $query->whereNull('until_date')
                                    ->orWhere(function ($query) use ($dates) {
                                        $query->whereNotNull('until_date')
                                                ->where('until_date', '>=', $dates[0]);
                            });
                        });
        }

        $cfgs = $cfgs->where($payTypeId == \SCons::PAY_W_Q ? 'is_biweek' : 'is_week', true)
                        ->orderBy('order_vobo', 'ASC')
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

                    $oDelegation = PrepayrollDelegation::where('user_delegation_id', $cfg->user_n_id)
                                    ->where('is_delete', false)
                                    ->where('pay_way_id', $payTypeId)
                                    ->where('number_prepayroll', $number)
                                    ->first();

                    $oVobo = $lVobo->first();

                    if ($oVobo != null) {
                        if ($oDelegation != null) {
                            $oVobo = PrepayReportControl::find($oVobo->id_control);
                            $oVobo->is_required = false;
                            $oVobo->save();
                        }
                        $orderBovo++;

                        continue;
                    }
                    
                    $prac->year = $year;
                    $prac->is_required = $oDelegation != null ? false : $cfg->is_required;
                    $prac->is_vobo = false;
                    $prac->dt_vobo = null;
                    $prac->is_rejected = false;
                    $prac->dt_rejected = null;
                    $prac->order_vobo = $orderBovo;
                    $prac->is_global = $cfg->is_global;
                    $prac->is_delete = false;
                    $prac->cfg_id = $cfg->id_configuration;
                    $prac->user_vobo_id = $cfg->user_n_id;
                    $prac->created_by = \Auth::user()->id;
                    $prac->updated_by = \Auth::user()->id;
    
                    $prac->save();

                    $orderBovo++;
                }
                else if ($cfg->rol_n_name != null) {
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
                        $prac->is_global = $cfg->is_global;
                        $prac->is_delete = false;
                        $prac->cfg_id = $cfg->id_configuration;
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
            \Log::error($th->getMessage());
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

        $lVobos = \DB::table('prepayroll_report_auth_controls AS prac')
                        ->join('users AS u', 'u.id', '=', 'prac.user_vobo_id');

        if ($payTypeId == \SCons::PAY_W_Q) {
            $lVobos = $lVobos->where('prac.is_biweek', true)
                            ->where('prac.num_biweek', $number[0]);
        }
        else {
            $lVobos = $lVobos->where('prac.is_week', true)
                            ->where('prac.num_week', $number[0]);
        }

        $lVobos = $lVobos->where('prac.is_delete', false)
                            ->where('prac.year', $number[1])
                            ->orderBy('prac.order_vobo', 'ASC');

        $lVobosEmpty = clone $lVobos;

        /**
         * Si no hay vobos, se retorna true
         */
        $lVobosEmpty = $lVobosEmpty->get();
        if (count($lVobosEmpty) == 0) {
            return true;
        }

        $lVoboUsr = clone $lVobos;
        $oVoboUsr = $lVoboUsr->where('prac.user_vobo_id', \Auth::user()->id)->first();

        if ($oVoboUsr != null) {
            $lVobosMaj = clone $lVobos;
            $lVobosMaj = $lVobosMaj->where('prac.order_vobo', '>', $oVoboUsr->order_vobo)
                            ->where('prac.is_vobo', true)
                            ->where('prac.user_vobo_id', '!=', \Auth::user()->id);

            $userGroups = SPrepayrollUtils::getUserGroups(\Auth::user()->id);

            $lUsersBosses = [];
            foreach ($userGroups as $group) {
                $aux = [];
                $aux[] = $group;
                $lGroups = SPrepayrollUtils::getAncestryOfGroups($aux);

                $users = \DB::table('prepayroll_groups AS pg')
                            ->join('prepayroll_groups_users AS pgu', 'pg.id_group', '=', 'pgu.group_id')
                            ->whereIn('id_group', $lGroups)
                            ->pluck('pgu.head_user_id')
                            ->toArray();

                $lUsersBosses = array_merge($lUsersBosses, $users);
            }

            $lVobosMaj = $lVobosMaj->whereIn('prac.user_vobo_id', $lUsersBosses)
                                    ->get();
            
            if (count($lVobosMaj) > 0) {
                $usersMaj = "";
                foreach ($lVobosMaj as $vobo) {
                    $usersMaj .= $vobo->name . ",";
                }

                return "Los usuarios ".$usersMaj." ya han aprobado esta prenómina.";
            }
        }

        $myVoBo = clone $lVobos;
        $myVoBo = $myVoBo->where('prac.is_vobo', true)
                        ->where('prac.user_vobo_id', \Auth::user()->id)
                        ->get();

        if (count($myVoBo) > 0) {
            return "Ya diste tu vobo de esta prenómina.";
        }
        
        return true;
    }

    public static function canMakeAdjustByEmployee($employee_id, $dtDate, $payTypeId){
        $number = SDateUtils::getNumberOfDate($dtDate, $payTypeId);

        $lVobos = \DB::table('prepayroll_report_emp_vobos AS prev')
                        ->where('employee_id', $employee_id);

        $week_biWeek = "semana/quincena";

        if ($payTypeId == \SCons::PAY_W_Q) {
            $week_biWeek = "quincena";
            $lVobos = $lVobos->where('prev.is_biweek', true)
                            ->where('prev.num_biweek', $number[0]);
        }
        else {
            $week_biWeek = "semana";
            $lVobos = $lVobos->where('prev.is_week', true)
                            ->where('prev.num_week', $number[0]);
        }

        $oVobo = $lVobos->where('prev.is_delete', false)
                            ->where('prev.year', $number[1])
                            ->orderBy('updated_at', 'DESC')
                            ->first();

        if (is_null($oVobo)) {
            return true;
        }
        else {
            if ($oVobo->is_vobo) {
                return "La ".$week_biWeek." tiene visto bueno para el empleado.";
            }
            else {
                // return "La ".$week_biWeek." tiene visto bueno rechazado para el empleado.";
                return true;
            }

        }
    }

    /**
     * Determinar si la nómina ha sido omitida y si es el caso marcarla como cerrada
     *
     * @param int $numPp
     * @param int $yearPp
     * @param int $tpPay
     * 
     * @return boolean
     */
    public static function prepayrollIsClosed($numPp, $yearPp, $tpPay)
    {
        /**
         * Revisar si el usuario que está dando VoBo ha autorizado una omisión de la nómina
         */
        $oPpSkp = prepayrollVoboSkipped::where('year', $yearPp);

        if ($tpPay == \SCons::PAY_W_S) {
            $oPpSkp = $oPpSkp->where('is_week', true)
                            ->where('num_week', $numPp);
        }
        else {
            $oPpSkp = $oPpSkp->where('is_biweek', true)
                            ->where('num_biweek', $numPp);
        }

        $oPpSkp = $oPpSkp->where('is_delete', 0)->first();

        return ! is_null($oPpSkp);
    }

    /**
     * Determina si la prenómina no tiene pendientes vistos buenos
     *
     * @param string $dtDate
     * @param string $dtDateEnd
     * @param integer $payTypeId
     * 
     * @return array
     */
    public static function isFreeVoboPrepayroll($dtDate, $dtDateEnd, $payTypeId)
    {
        $prepayrollAprovedd = [];
        $oStartDate = Carbon::parse($dtDate);
        $oEndDate = Carbon::parse($dtDateEnd);
        $oDate = clone $oStartDate;

        /**
         * crea un arreglo con los días a consultar
         */
        while ($oDate->lessThanOrEqualTo($oEndDate)) {
            $number = SDateUtils::getNumberOfDate($oDate->toDateString(), $payTypeId);

            if (in_array($number[0], $prepayrollAprovedd)) {
                $oDate->addDay();
                continue;
            }

            $isFree = SPrepayrollUtils::isPayrollNumberFreeOfVobo($number[0], $number[1], $payTypeId);

            if ($isFree) {
                $prepayrollAprovedd[] = $number[0];
            }
            else {
                return [false, $oDate->toDateString()];
            }

            $oDate->addDay();
        }

        return [true, $prepayrollAprovedd];
    }
    
}
