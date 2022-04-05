<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;

use App\Models\prepayrollAdjust;
use App\Models\prepayrollAuthControl;
use App\Models\employees;
use App\SUtils\SPrepayrollAdjustUtils;

class prepayrollAdjustController extends Controller
{
    public function indexLog(Request $request)
    {
        $startDate = $request->start_date == null ? Carbon::now()->firstOfMonth()->toDateString() : $request->start_date;
        $endDate = $request->end_date == null ? Carbon::now()->lastOfMonth()->toDateString() : $request->end_date;

        $lAdjs = $this->getAdjustsQuery($startDate, $endDate);
        $lAdjs = $lAdjs->join('users AS ua', 'pac.user_auth_id', '=', 'ua.id')
                        ->leftJoin('users AS ur', 'pac.rejected_by', '=', 'ur.id')
                        ->select('pa.employee_id',
                                    'pa.dt_date',
                                    'pa.dt_time',
                                    'pa.minutes',
                                    'pa.comments',
                                    'pa.adjust_type_id',
                                    'pa.apply_to',
                                    'pat.type_code',
                                    'pat.type_name',
                                    'pa.id',
                                    'pac.id_control',
                                    'pac.user_auth_id',
                                    'pac.is_authorized',
                                    'pac.dt_authorization',
                                    'pac.is_rejected',
                                    'e.num_employee',
                                    'e.name AS employee',
                                    'u.name AS made_by',
                                    'ua.name AS auth_by',
                                    'ur.name AS rej_by'
                                )
                        ->distinct();
        $lAdjs = $lAdjs->get();

        return view('prepayroll.log')->with('lAdjs', $lAdjs)
                                            ->with('startDate', $startDate)
                                            ->with('endDate', $endDate);
    }

    /**
     * Autorizar ajustes por los supervisores y empleados con la facultad para ello
     *
     * @param Type|null $var
     * @return void
     */
    public function indexAuthorize(Request $request)
    {
        $startDate = $request->start_date == null ? Carbon::now()->firstOfMonth()->toDateString() : $request->start_date;
        $endDate = $request->end_date == null ? Carbon::now()->lastOfMonth()->toDateString() : $request->end_date;

        $lAdjs = $this->getAdjustsQuery($startDate, $endDate);
        $lAdjs = $lAdjs->where('pac.user_auth_id', \Auth::user()->id)
                        ->select('pa.employee_id',
                                'pa.dt_date',
                                'pa.dt_time',
                                'pa.minutes',
                                'pa.comments',
                                'pa.adjust_type_id',
                                'pa.apply_to',
                                'pat.type_code',
                                'pat.type_name',
                                'pa.id',
                                'pac.id_control',
                                'pac.user_auth_id',
                                'e.num_employee',
                                'e.name AS employee',
                                'u.name AS made_by'
                                )
                        ->distinct();

        $lAdjs = $lAdjs->get();
        
        // dd($lAdjs);

        return view('prepayroll.authindex')->with('lAdjs', $lAdjs)
                                            ->with('startDate', $startDate)
                                            ->with('endDate', $endDate);
    }

    private function getAdjustsQuery($startDate, $endDate)
    {
        $lAdjs = \DB::table('prepayroll_adjusts AS pa')
                        ->join('prepayroll_adjusts_types AS pat', 'pa.adjust_type_id', '=', 'pat.id')
                        ->join('prepayroll_auth_controls AS pac', 'pa.id', '=', 'pac.prepayroll_adjust_id')
                        ->join('employees AS e', 'pa.employee_id', '=', 'e.id')
                        ->join('users AS u', 'pa.created_by', '=', 'u.id')
                        ->whereBetween('dt_date', [$startDate, $endDate])
                        ->where('pa.is_delete', false)
                        ->where('pac.is_delete', false);

        return $lAdjs;
    }

    public function getAdjustsFromRow(Request $request)
    {
        $lAdjusts = \DB::table('prepayroll_adjusts AS pa')
                        ->join('prepayroll_adjusts_types AS pat', 'pa.adjust_type_id', '=', 'pat.id')
                        ->select('pa.employee_id',
                                    'pa.dt_date',
                                    'pa.dt_time',
                                    'pa.minutes',
                                    'pa.adjust_type_id',
                                    'pa.apply_to',
                                    'pa.comments',
                                    'pat.type_code',
                                    'pat.type_name',
                                    'pa.id'
                                    )
                        ->whereBetween('dt_date', [$request->start_date, $request->end_date])
                        ->where('is_delete', false)
                        ->where('pa.employee_id', $request->employee_id)
                        ->get();
        $config = \App\SUtils\SConfiguration::getConfigurations();

        if ($config->enabledAdjAuths) {
            $lAuthAdjusts = [];
            foreach ($lAdjusts as $adj) {
                if (SPrepayrollAdjustUtils::isAdjustAuthorized($adj->id)) {
                    $lAuthAdjusts[] = $adj;
                }
            }
    
            return json_encode($lAuthAdjusts);
        }

        return json_encode($lAdjusts);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function storeAdjust(Request $request)
    {
        $oAdjust = new prepayrollAdjust($request->all());
        
        $oAdjust->is_delete = false;
        $oAdjust->created_by = \Auth::user()->id;
        $oAdjust->updated_by = \Auth::user()->id;

        $oEmployee = employees::find($oAdjust->employee_id);
        $canMakeAdjust = PrepayrollReportController::canMakeAdjust($oAdjust->dt_date, $oEmployee->way_pay_id);
        if (is_string($canMakeAdjust)) {
            return json_encode(['success' => false, 'msg' => 'No se puede aplicar el ajuste, la prenómina tiene Vobo.'.$canMakeAdjust]);
        }

        try {
            \DB::beginTransaction();

                $oAdjust->save();

                $config = \App\SUtils\SConfiguration::getConfigurations();

                if ($config->enabledAdjAuths) {
                    $this->storeAuthControl($oAdjust->employee_id, $oAdjust->id);
                }

            \DB::commit();
        }
        catch (\Throwable $th) {
            \DB::rollBack();
            return json_encode(['success' => false, 'msg' => $e->getMessage()]);
        }

        SPrepayrollAdjustUtils::verifyProcessedData($oAdjust->employee_id, $oAdjust->dt_date);

        return json_encode(['success' => true, 'msg' => 'Ajuste aplicado correctamente.', 'data' => $oAdjust]);
    }

    public function deleteAdjust($idAjust)
    {
        $oAdjust = prepayrollAdjust::find($idAjust);

        $oAdjust->is_delete = true;
        $oAdjust->updated_by = \Auth::user()->id;

        $oAdjust->save();

        $config = \App\SUtils\SConfiguration::getConfigurations();

        if ($config->enabledAdjAuths) {
            $this->deleteAdjustAuthControl($idAjust);
        }

        SPrepayrollAdjustUtils::verifyProcessedData($oAdjust->employee_id, $oAdjust->dt_date);

        return json_encode($oAdjust);
    }

    private function storeAuthControl($employeeId, $idAdjust)
    {
        $roles = \Auth::user()->roles;

        $isSupervisor = false;
        foreach ($roles as $rol) {
            if ($rol->id == 2) {
                $isSupervisor = true;
                break;
            }
        }

        if (! $isSupervisor) {
            return;
        }

        $lCfgs = \DB::table('prepayroll_auth_configs AS cf')
                        ->where('is_delete', 0)
                        ->where('user_id', '!=', \Auth::user()->id)
                        ->get();

        $lUsers = [];
        $lControls = [];
        foreach ($lCfgs as $config) {
            if (in_array($config->user_id, $lUsers)) {
                continue;
            }

            $control = new prepayrollAuthControl();

            $control->prepayroll_adjust_id = $idAdjust;
            $control->user_auth_id = $config->user_id;
            $control->is_authorized = false;
            $control->dt_authorization = null;
            $control->is_delete = 0;
            $control->created_by = \Auth::user()->id;
            $control->updated_by = \Auth::user()->id;

            $lControls[] = $control;
            $lUsers[] = $config->user_id;
        }

        // $lAuths = \DB::table('prepayroll_group_employees AS pge')
        //                 ->join('prepayroll_groups AS pg', 'pge.group_id', '=', 'pg.id_group')
        //                 ->select('pg.head_user_id')
        //                 ->where('pge.is_delete', 0)
        //                 ->where('pg.is_delete', 0)
        //                 ->where('pge.employee_id', $employeeId)
        //                 ->where('pg.head_user_id', '!=', \Auth::user()->id)
        //                 ->distinct()
        //                 ->get();

        // foreach ($lAuths as $head) {
        //     if (in_array($head->head_user_id, $lUsers)) {
        //         continue;
        //     }

        //     $control = new prepayrollAuthControl();

        //     $control->prepayroll_adjust_id = $idAdjust;
        //     $control->user_auth_id = $head->head_user_id;
        //     $control->is_authorized = false;
        //     $control->dt_authorization = null;
        //     $control->is_delete = 0;
        //     $control->created_by = \Auth::user()->id;
        //     $control->updated_by = \Auth::user()->id;

        //     $lControls[] = $control;
        //     $lUsers[] = $head->head_user_id;
        // }

        foreach ($lControls as $control) {
            $control->save();
        }
    }

    private function deleteAdjustAuthControl($idAdjust)
    {
        prepayrollAuthControl::where('prepayroll_adjust_id', $idAdjust)
                                ->update(['is_delete' => 0]);
    }

    /**
     * Autoriza el ajuste recibido
     *
     * @param Request $request
     * @return void
     */
    public function authorizeAdjust($id)
    {
        $control = prepayrollAuthControl::find($id);

        $control->is_authorized = true;
        $control->dt_authorization = Carbon::now()->toDateTimeString();
        $control->is_rejected = false;
        $control->rejected_by = null;

        $control->save();

        return redirect()->route('ajustes_x_autorizar')->with('mensaje', 'Ajuste autorizado.');
    }

    /**
     * Rechaza el ajuste recibido
     *
     * @param Request $request
     * @return void
     */
    public function rejectAdjust($id)
    {
        $control = prepayrollAuthControl::find($id);

        $control->is_authorized = false;
        $control->dt_authorization = null;
        $control->is_rejected = true;
        $control->rejected_by = \Auth::user()->id;

        $control->save();

        return redirect()->route('ajustes_x_autorizar')->with('mensaje', 'Ajuste rechazado.');
    }

}
