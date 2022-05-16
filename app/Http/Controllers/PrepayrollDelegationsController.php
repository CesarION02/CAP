<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\UserPermission;
use App\Models\PrepayrollDelegation;
use App\Models\User;
use App\Models\PrepayReportConfig;
use App\Models\PrepayReportControl;

use App\SUtils\SDateUtils;
use App\SUtils\SPermissions;
use App\SUtils\SPrepayrollUtils;

class PrepayrollDelegationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $lDelegations = \DB::table('prepayroll_report_delegations AS prd')
                            ->join('users AS ul', 'ul.id', '=', 'prd.user_delegation_id')
                            ->join('users AS ud', 'ud.id', '=', 'prd.user_delegated_id')
                            ->join('users AS uc', 'uc.id', '=', 'prd.user_insert_id')
                            ->join('users AS uu', 'uu.id', '=', 'prd.user_update_id')
                            ->select('prd.*', 
                                    'ul.name AS user_delegation_name', 
                                    'ud.name AS user_delegated_name', 
                                    'uc.name AS user_insert_name', 
                                    'uu.name AS user_update_name')
                            ->where('prd.is_delete', false)
                            ->get();

        return view('prepayroll.delegation.index', [
            'lDelegations' => $lDelegations
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $lDelegationUsers = \DB::table('prepayroll_report_configs as prc')
                                ->join('users as u', 'prc.user_n_id', '=', 'u.id')
                                ->whereNotNull('prc.user_n_id')
                                ->where('u.is_delete', false)
                                ->select('u.id', 'u.name')
                                ->distinct()
                                ->orderBy('u.name', 'asc')
                                ->get();

        $lToDelegationUsers = \DB::table('users')
                                ->where('is_delete', false)
                                ->select('id', 'name')
                                ->distinct()
                                ->orderBy('name', 'asc')
                                ->get();

        $year = date('Y');
        $oDates = SDateUtils::getCutoffDatesOfYear($year);

        return view('prepayroll.delegation.create', [
            'lDelegationUsers' => $lDelegationUsers,
            'lToDelegationUsers' => $lToDelegationUsers,
            'oDates' => $oDates
        ]);
    }

    /**
     * 
     *
     * @param Request $request
     * @return boolean
     */
    public function isDelegationValid(Request $request)
    {
        $oDel = \DB::table('prepayroll_report_delegations')
                        ->where('number_prepayroll', $request->number_prepayroll)
                        ->where('year', $request->year)
                        ->where('pay_way_id', $request->pay_way_id)
                        ->where('user_delegation_id', $request->user_delegation_id)
                        ->where('is_delete', false)
                        ->first();

        $response = [
            'code' => $oDel == null ? 404 : 200,
            'status' => $oDel == null ? 'not found' : 'found',
            'data' => $oDel
        ];

        return json_encode($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        try {
            \DB::beginTransaction();
            $config = \App\SUtils\SConfiguration::getConfigurations(); // Obtengo las configuraciones del sistema

            $oDelegation = new PrepayrollDelegation($request->all());
            $payroll = explode("_", $request->number_prepayroll);
            $oDelegation->number_prepayroll = $payroll[0];
            $oDelegation->year = $payroll[1];
            $oDelegation->is_active = true;
            $oDelegation->is_delete = false;
            $oDelegation->pay_way_id = $request->pay_way_id;
            $oDelegation->user_delegation_id = $request->user_delegation_id;
            $oDelegation->user_delegated_id = $request->user_delegated_id;
            $oDelegation->user_insert_id = \Auth::user()->id;
            $oDelegation->user_update_id = \Auth::user()->id;
            
            $oUserDelegation = User::find($request->user_delegation_id);
            $oUserDelegated = User::find($request->user_delegated_id);

            $oJsonObj = new \stdClass();
            $oJsonObj->role = null;
            $oJsonObj->user_permission_id = null;
            $oJsonObj->prepay_report_config = null;
            $oJsonObj->prepay_groups_user = [];

            $reportConfig = \DB::table('prepayroll_report_configs')
                                ->where('user_n_id', $oDelegation->user_delegation_id)
                                ->where($request->pay_way_id == \SCons::PAY_W_Q ? 'is_biweek' : 'is_week', true)
                                ->where('is_delete', false)
                                ->first();

            if ($reportConfig == null) {
                throw new \Exception('No se encontró configuración de '.($request->pay_way_id == \SCons::PAY_W_Q ? 'quincena' : 'semana').
                                        ' del reporte de TE para el usuario '.$oUserDelegation->name.'.');
            }

            $dates = SDateUtils::getDatesOfPayrollNumber($oDelegation->number_prepayroll, $oDelegation->year, $request->pay_way_id);

            $oCfg = new PrepayReportConfig();

            $oCfg->since_date = $dates[0];
            $oCfg->until_date = $dates[1];
            $oCfg->is_week = $oDelegation->pay_way_id == \SCons::PAY_W_S;
            $oCfg->is_biweek = $oDelegation->pay_way_id == \SCons::PAY_W_Q;
            $oCfg->is_required = $reportConfig->is_required;
            $oCfg->order_vobo = $reportConfig->order_vobo;
            $oCfg->rol_n_name = null;
            $oCfg->user_n_id = $oDelegation->user_delegated_id;
            $oCfg->is_delete = false;
            $oCfg->created_by = \Auth::user()->id;
            $oCfg->updated_by = \Auth::user()->id;

            $oCfg->save();

            $oJsonObj->prepay_report_config = $oCfg->id_configuration;
            $role = 0;
            // Menú de reporte de tiempo extra delegado
            $res = SPermissions::assignMenuByDefaultRol($oDelegation->user_delegated_id, $config->idOverTimeMenuDel);
            if ($res == null) {
                throw new \Exception('No se pudo asignar el menú de reporte de tiempo extra delegado al usuario '.$oUserDelegated->name.'.');
            }
            else if ($res > 0) {
                $role = $res;
            }

            if ($role == 0) {
                // Menú de vobos semanales
                $res = SPermissions::assignMenuByDefaultRol($oDelegation->user_delegated_id, $config->idVoboWeekMenu);
                if ($res == null) {
                    throw new \Exception('No se pudo asignar el menú de vistos buenos semana al usuario '.$oUserDelegated->name.'.');
                }
                else if ($res > 0) {
                    $role = $res;
                }

                if ($role == 0) {
                    // Menú de vobos quincenales
                    $res = SPermissions::assignMenuByDefaultRol($oDelegation->user_delegated_id, $config->idVoboBiWeekMenu);
                    if ($res == null) {
                        throw new \Exception('No se pudo asignar el menú de vistos buenos quincena al usuario '.$oUserDelegated->name.'.');
                    }
                    else if ($res > 0) {
                        $role = $res;
                    }
                }
            }

            // si se asignó el rol
            if ($role > 0) {
                $oJsonObj->role = $role;
            }

            // Permiso de ajustes para el reporte de tiempo extra
            $hasPermission = SPermissions::hasPermissionById($request->user_delegated_id, $config->idAdjsPermission);

            if (! $hasPermission ) {
                $oPermission = new UserPermission();
                $oPermission->user_id = $request->user_delegated_id;
                $oPermission->permission_id = $config->idAdjsPermission;
                $oPermission->by_system = true;

                $oPermission->save();

                $oJsonObj->user_permission_id = $oPermission->id;
            }
            
            // Obtener grupos de prenómina del usuario que delega
            $groups = SPrepayrollUtils::getUserGroups($request->user_delegation_id)->toArray();
            $oJsonObj->prepay_groups_user = $groups;

            $oDelegation->json_insertions = json_encode($oJsonObj);

            $oDelegation->save();

            \DB::commit();
        }
        catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['Error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('prepayrolldelegation.index')->with('success', 'Delegación de V.º B.º de prenómina creada correctamente');
    }

    /**
     * 
     */
    public function delete(Request $request, $idDelegation)
    {
        $oDelegation = PrepayrollDelegation::find($idDelegation);

        if ($oDelegation == null) {
            return redirect()->back()->withErrors(['Error' => 'No se encontró la delegación de V.º B.º de prenómina.']);
        }

        try {
            \DB::beginTransaction();

            $oObjInsertions = json_decode($oDelegation->json_insertions);
    
            $oReportCfg = PrepayReportConfig::find($oObjInsertions->prepay_report_config);
            if ($oReportCfg == null) {
                throw new \Exception('No se encontró la configuración de reporte de V.º B.º de prenómina.');
            }
            $oReportCfg->is_delete = true;
            $oReportCfg->save();

            $lVobos = PrepayReportControl::where('cfg_id', $oObjInsertions->prepay_report_config)
                                            ->where('is_delete', false)
                                            ->get();

            foreach ($lVobos as $oVobo) {
                $oVobo->is_delete = true;
                $oVobo->save();
            }

            if ($oObjInsertions->role > 0) {
                \DB::table('user_rol')->where('rol_id', $oObjInsertions->role)->delete();
            }
            if ($oObjInsertions->user_permission_id > 0) {
                \DB::table('user_permission')->where('id', $oObjInsertions->user_permission_id)->delete();
            }
    
            $oDelegation->is_active = false;
            $oDelegation->is_delete = true;
            $oDelegation->user_update_id = \Auth::user()->id;
    
            $oDelegation->save();

            \DB::commit();
        }
        catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withErrors(['Error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('prepayrolldelegation.index')->with('success', 'Delegación de V.º B.º de prenómina eliminada correctamente');
    }
}
