<?php namespace App\SUtils;

use App\Mail\RejectedVoboNotification;
use App\Models\PrepayReportConfig;
use App\Models\User;
use App\SUtils\SPayrollDelegationUtils;
use DB;
use Carbon\Carbon;
use App\Models\prepayrollVoboSkipped;
use App\Models\PrepayReportControl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
class SPrepayrollUtils {

    /**
     * Obtiene los empleados correspondientes al grupo asignado al usuario recibido y a los grupos
     * dependientes del mismo.
     *
     * @param int $idUser
     * @param int $payType
     * @param bool $bDirect
     * @param int $iDelegations si es null no se consideran delegaciones, 
     *                          si es 0 se consideran todas las delegaciones del usuario, 
     *                          si es mayor que 0 se considera como el id de la delegacion
     * 
     * @return array
     */
    public static function getEmployeesByUser($idUser, $payType, $bDirect, $iDelegation = null) {
        $roles = \DB::table('rol as r')
                    ->join('user_rol as ur', 'ur.rol_id', 'r.id')
                    ->where('ur.user_id', $idUser)
                    ->select('r.id','ur.user_id')
                    ->get();

        $config = \App\SUtils\SConfiguration::getConfigurations(); // Obtengo las configuraciones del sistema

        $seeAll = false;
        foreach ($roles as $rol) {
            if (in_array($rol->id, $config->rolesCanSeeAll)) {
                $seeAll = true;
                break;
            }
        }

        if ($seeAll) {
            $lEmployeesGroup = \DB::table('prepayroll_group_employees as pge')
                                ->join('employees as e', 'e.id', '=', 'pge.employee_id')
                                ->where('pge.is_delete', 0)
                                ->where('e.is_delete', 0)
                                ->where('e.is_active', 1)
                                ->where('e.way_pay_id', $payType)
                                ->pluck('e.id');

            $lDeptEmployees = \DB::table('prepayroll_group_deptos as pgd')
                                ->join('employees as e', 'e.department_id', '=', 'pgd.department_id')
                                ->where('e.is_delete', 0)
                                ->where('e.is_active', 1)
                                ->where('e.way_pay_id', $payType)
                                ->pluck('e.id');

            $merge = $lEmployeesGroup->merge($lDeptEmployees);
            $unique = $merge->unique();
            $lEmployees = $unique->toArray();
            
            return $lEmployees;
        }

        // Obtiene los grupos de prenómina que el usuario puede ver
        if ($iDelegation == null) {
            $groups = \DB::table('prepayroll_groups_users AS pgu')
                                ->where('pgu.head_user_id', $idUser)
                                ->pluck('pgu.group_id')
                                ->toArray();
        }
        else {
            if ($iDelegation == 0) {
                // obtiene los grupos de todas las delegaciones de prenómina
                $groups = SPayrollDelegationUtils::getGroupsAllDelegations($idUser);
            }
            else if ($iDelegation > 0) {
                // obtiene los grupos de la delegación de prenómina indicada
                $groups = SPayrollDelegationUtils::getGroupsOfDelegation($idUser, $iDelegation);
            }
        }

        // Obtiene los empleados que pertenecen a los grupos de prenómina
        return SPrepayrollUtils::getEmployeesOfGroups($groups, $payType, $bDirect);
    }

    /**
     * Obtiene los empleados correspondientes a los grupos de prenómina recibidos.
     *
     * @param array $groups
     * @param int $payType
     * @param boolean $bDirect
     * 
     * @return void
     */
    public static function getEmployeesOfGroups($groups, $payType, $bDirect)
    {
        if (! $bDirect) {
            // Obtiene los sub-grupos de los grupos directos
            $lGroups = SPrepayrollUtils::getChildrenOfGroups($groups);
        }
        else {
            $lGroups = $groups;
        }

        // Obtiene los empleados pertenecientes a todos los grupos que el usuario puede ver
        $aEmployees = \DB::table('prepayroll_groups AS pg')
                        ->join('prepayroll_group_employees AS pge', 'pg.id_group', '=', 'pge.group_id')
                        ->join('employees AS e', 'pge.employee_id', '=', 'e.id')
                        ->where('e.is_delete', false)
                        ->where('e.is_active', true);

        if ($payType > 0) {
            $aEmployees = $aEmployees->where('e.way_pay_id', $payType);
        }

        $aEmployees = $aEmployees->where('e.is_delete', false)
                                ->whereIn('pg.id_group', $lGroups)
                                ->pluck('pge.employee_id')
                                ->toArray();

        // Obtiene los empleados pertenecientes a los grupos que el usuario no puede ver
        $aEmployeesOthers = \DB::table('prepayroll_groups AS pg')
                            ->join('prepayroll_group_employees AS pge', 'pg.id_group', '=', 'pge.group_id')
                            ->join('employees AS e', 'pge.employee_id', '=', 'e.id')
                            ->where('e.is_active', true)
                            ->where('e.is_delete', false);
                        
        if ($payType > 0) {
            $aEmployeesOthers = $aEmployeesOthers->where('e.way_pay_id', $payType);
        }

        $aEmployeesOthers = $aEmployeesOthers->whereNotIn('pg.id_group', $lGroups)
                                            ->pluck('pge.employee_id')
                                            ->toArray();

        // Obtiene los departamentos asignados a los grupos que el usuario puede ver
        $deptsOfPPGroups = \DB::table('prepayroll_group_deptos AS pgd')
                                ->whereIn('pgd.group_id', $lGroups)
                                ->pluck('pgd.department_id')
                                ->toArray();

        // Obtiene los empleados asignados por departamento que no estén asignados ya directamente a otros grupos
        $deptEmployees = \DB::table('employees AS e')
                                ->join('departments AS d', 'e.department_id', '=', 'd.id')
                                ->where('e.is_active', true)
                                ->where('e.is_delete', false);
                        
        if ($payType > 0) {
            $deptEmployees = $deptEmployees->where('e.way_pay_id', $payType);
        }

        $deptEmployees = $deptEmployees->whereIn('d.id', $deptsOfPPGroups)
                                        ->whereNotIn('e.id', $aEmployeesOthers)
                                        ->pluck('e.id')
                                        ->toArray();

        // Unifica los empleados de los grupos y los empleados asignados por departamento
        $aEmployeesAll = array_merge($deptEmployees, $aEmployees);

        // Elimina los empleados repetidos
        return array_unique($aEmployeesAll);
    }

    /**
     * Obtiene los grupos dependientes de los grupos recibidos.
     *
     * @param array $groups
     * @return array
     */
    public static function getChildrenOfGroups($groups) {
        $lGroups = [];
        $lGroups = array_merge($lGroups, $groups);
        foreach ($groups as $group) {
            $children = SPrepayrollUtils::getChildren($group);
            if (count($children) > 0) {
                $childrenGroups = SPrepayrollUtils::getChildrenOfGroups($children);
                $lGroups = array_merge($lGroups, $childrenGroups);
            }
        }

        return array_unique($lGroups);
    }

    /**
     * Obtiene los grupos de los que usuario recibido es encargado.
     */
    public static function getUserGroups($idUser = 0)
    {
        if ($idUser == 0) {
            $idUser = \Auth::user()->id;
        }

        $lGroups = \DB::table('prepayroll_groups AS pg')
                        ->join('prepayroll_groups_users AS pgu', 'pg.id_group', '=', 'pgu.group_id')
                        ->where('pgu.head_user_id', $idUser)
                        ->pluck('pg.id_group');

        return $lGroups;
    }

    /**
     * Obtiene los grupos dependientes del grupo recibido.
     *
     * @param int $group
     * @return array
     */
    private static function getChildren($idGroup) {
        $children = \DB::table('prepayroll_groups AS pg')
                        ->where('pg.father_group_n_id', $idGroup)
                        ->pluck('pg.id_group')
                        ->toArray();

        return $children;
    }

    /**
     * Obtiene los grupos dependientes de los grupos recibidos.
     *
     * @param array $groups
     * @return void
     */
    public static function getAncestryOfGroups($groups) {
        $lGroups = [];
        $lGroups = array_merge($lGroups, $groups);
        foreach ($groups as $group) {
            $ancestries = SPrepayrollUtils::getAncestryGroups($group);
            if (count($ancestries) > 0) {
                $ancestryGroups = SPrepayrollUtils::getAncestryOfGroups($ancestries);
                $lGroups = array_merge($lGroups, $ancestryGroups);
            }
        }

        return array_unique($lGroups);
    }

    /**
     * Obtiene los grupos superiores del grupo recibido.
     *
     * @param int $group
     * @return void
     */
    private static function getAncestryGroups($idGroup) {
        $father = \DB::table('prepayroll_groups AS pg')
                        ->where('pg.id_group', $idGroup)
                        ->pluck('pg.father_group_n_id')
                        ->toArray();

        return $father;
    }

    public static function isValidGroupHeredity($idGroup, $fatherGroupId)
    {
        if ($fatherGroupId > 0) {
            $fathers = SPrepayrollUtils::getAncestryOfGroups([(int) $fatherGroupId]);
        }
        else {
            $fathers = [];
        }

        if ($idGroup > 0) {
            $children = SPrepayrollUtils::getChildrenOfGroups([(int) $idGroup]);
        }
        else {
            $children = [];
        }

        $groups = array_merge($fathers, $children);
        $lGroups = collect($groups);
        $lDuplicates = $lGroups->duplicates();
        if (count($lDuplicates) > 0) {
            return false;
        }

        return true;
    }

    public static function isAllEmployeesOk($idUser, $idVobo, $iDelegation = null)
    {
        $prepayroll = \DB::table('prepayroll_report_auth_controls AS prac')
                            ->where('prac.user_vobo_id', $idUser)
                            ->where('prac.id_control', $idVobo)
                            ->first();

        if ($prepayroll == null) {
            return false;
        }

        if (! env('VOBO_BY_EMP_ENABLED', true)) {
            return true;
        }
        
        $payType = $prepayroll->is_week ? \SCons::PAY_W_S : \SCons::PAY_W_Q;
        $number = $prepayroll->is_week ? $prepayroll->num_week : $prepayroll->num_biweek;
        
        $bDirectEmployees = true;
        $aEmployees = SPrepayrollUtils::getEmployeesByUser($idUser, $payType, $bDirectEmployees, $iDelegation);

        $aEmployeesOk = \DB::table('prepayroll_report_emp_vobos AS empvb')
                            ->where('empvb.year', $prepayroll->year);

        if ($aEmployees != null && is_array($aEmployees)) {
            $aEmployeesOk = $aEmployeesOk->whereIn('empvb.employee_id', $aEmployees);
        }
        
        if ($payType == \SCons::PAY_W_S) {
            $aEmployeesOk = $aEmployeesOk->where('empvb.num_week', $number);
        }
        else {
            $aEmployeesOk = $aEmployeesOk->where('empvb.num_biweek', $number);
        }

        $aEmployeesOk = $aEmployeesOk->where('empvb.is_delete', false)
                                    ->where('empvb.is_vobo', true)
                                    ->pluck('empvb.employee_id')
                                    ->toArray();

        $dates = SDateUtils::getDatesOfPayrollNumber($number, $prepayroll->year, $payType);
        $lEmployees = collect($aEmployees);
        $lEmployees = SReportsUtils::filterEmployeesByAdmissionDate($lEmployees, $dates[1], null);
        $lEmployeesOk = collect($aEmployeesOk);
        $lEmployeesOk = SReportsUtils::filterEmployeesByAdmissionDate($lEmployeesOk, $dates[1], null);

        $lEmployeesNotOk = $lEmployees->diff($lEmployeesOk);

        if (count($lEmployeesNotOk) > 0) {
            return false;
        }

        return true;
    }

    public static function isAdvancedDate($idVobo){
        // sacar cual es la prenomina.
        $prepayroll = DB::table('prepayroll_report_auth_controls')->where('id_control',$idVobo)->get();

        // ver si es semana o quincena
        if($prepayroll[0]->is_week == 1){
            //semana
            $fechaPrepayroll = DB::table('week_cut')->where('num',$prepayroll[0]->num_week)->where('year',$prepayroll[0]->year)->get();
            $fechaComparacion = $fechaPrepayroll[0]->fin;
        }else{
            //quincena
            $fechaPrepayroll = DB::table('hrs_prepay_cut')->where('num',$prepayroll[0]->num_biweek)->where('year',$prepayroll[0]->year)->get();
            $fechaComparacion = $fechaPrepayroll[0]->dt_cut;
        }
        
        $fechaComparacion = Carbon::parse($fechaComparacion);
        $fechaActual = Carbon::now();
        $mayor = $fechaActual->greaterThan($fechaComparacion);  
        return $mayor;

    }

    /**
     * Undocumented function
     *
     * @param integer $prepayrollNumber
     * @param integer $prepayrollYear
     * @param integer $payTypeId
     * 
     * @return boolean
     */
    public static function isPayrollNumberFreeOfVobo($prepayrollNumber, $prepayrollYear, $payTypeId) {
        $lVobos = \DB::table('prepayroll_report_auth_controls AS prac');

        if ($payTypeId == \SCons::PAY_W_Q) {
            $lVobos = $lVobos->where('is_biweek', true)
                            ->where('num_biweek', $prepayrollNumber);
        }
        else {
            $lVobos = $lVobos->where('is_week', true)
                            ->where('num_week', $prepayrollNumber);
        }

        $lVobos = $lVobos->where('is_delete', false)
                            ->where('year', $prepayrollYear)
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
            $lSkipped = prepayrollVoboSkipped::where('year', $prepayrollYear)
                                                ->where('is_delete', 0);

            if ($payTypeId == \SCons::PAY_W_Q) {
                $lSkipped = $lSkipped->where('is_biweek', true)
                                    ->where('num_biweek', $prepayrollNumber);
            }
            else {
                $lSkipped = $lSkipped->where('is_week', true)
                                    ->where('num_week', $prepayrollNumber);
            }
            
            $lSkipped = $lSkipped->orderBy('created_at', 'DESC')
                                ->first();

            if ($lSkipped != null) {
                $voBo = PrepayReportControl::where('user_vobo_id', $lSkipped->skipped_by_id)
                                    ->where('year', $prepayrollYear)
                                    ->where('is_delete', 0)
                                    ->where('is_vobo', true);

                if ($payTypeId == \SCons::PAY_W_Q) {
                    $voBo = $voBo->where('is_biweek', true)
                                ->where('num_biweek', $prepayrollNumber);
                }
                else {
                    $voBo = $voBo->where('is_week', true)
                                ->where('num_week', $prepayrollNumber);
                }

                $voBo = $voBo->get();

                if (count($voBo) > 0) {
                    return true;
                }
            }

            return false;
        }
        
        return true;
    }

    public static function getUsersOfUser($idUser)
    {
        // obtiene los grupos del usuario
        $groups = SPrepayrollUtils::getUserGroups($idUser);
        // obtiene los usuarios dependientes del usuario recibido
        $lChildrenGroups = SPrepayrollUtils::getChildrenOfGroups($groups->toArray());

        $lChildrenGroups = array_diff($lChildrenGroups, $groups->toArray());
    
        $lGroupsHeads = DB::table('prepayroll_groups AS pg')
                                    ->join('prepayroll_groups_users AS pgu', 'pg.id_group', '=', 'pgu.group_id')
                                    ->join('users AS u', 'pgu.head_user_id', '=', 'u.id')
                                    ->whereIn('pg.id_group', $lChildrenGroups)
                                    ->where('pgu.head_user_id', '<>', auth()->user()->id)
                                    ->select('pgu.head_user_id')
                                    ->distinct()
                                    ->pluck('pgu.head_user_id');
        
        return $lGroupsHeads;
    }

    public static function notifyToUsers($idPrepayrollCtrl, $idUser, $reason)
    {
        $users = self::getUsersOfUser($idUser);

        // obtiene los correos de los usuarios recibidos
        $lMails = User::whereIn('id', $users)->where('email', '!=', "")
                                            ->whereNotNull('email')
                                            ->where('is_delete', 0)
                                            ->pluck('email');

        // Enviar correo a los usuarios
        $userReject = User::find($idUser)->name;
        $oCtrl = PrepayReportControl::find($idPrepayrollCtrl);
        $wayPay = $oCtrl->is_week ? "Semana" : "Quincena";
        $iWayPay = $oCtrl->is_week ? \SCons::PAY_W_S : \SCons::PAY_W_Q;
        $prepayrollNum = $oCtrl->is_week ? $oCtrl->num_week : $oCtrl->num_biweek;
        $aDates = SDateUtils::getDatesOfPayrollNumber($prepayrollNum, $oCtrl->year, $iWayPay);
        $startDate = $aDates[0];
        $endDate = $aDates[1];

        try {
            // Mail::to($lMails)->send(new RejectedVoboNotification($userReject, $wayPay, $prepayrollNum, $startDate, $endDate, $reason));
            $g = $lMails;
        }
        catch (\Throwable $th) {
            \Log::error($th);
        }
    }

    private static function findDepth($node) {
        if (empty($node->lGroups)) {
            return 1; // El nodo hoja tiene profundidad 1
        }
        else {
            $maxDepth = 0;
            foreach ($node->lGroups as $child) {
                $childDepth = SPrepayrollUtils::findDepth($child);
                $maxDepth = max($maxDepth, $childDepth);
            }
            return $maxDepth + 1; // La profundidad del nodo actual es la máxima profundidad de sus hijos + 1
        }
    }

    public static function setBranchAndLevelByGroup() {
        $lTree = SPrepayrollUtils::buildTree();
        
        // Quincena
        $branch = 1;
        foreach ($lTree as $oTree) {
            $maxLevel = SPrepayrollUtils::findDepth($oTree);
            $oTree = SPrepayrollUtils::setLevelAndBranch($oTree, $branch, \SCons::PAY_W_Q, $maxLevel);
            $branch++;
        }

        // Semana
        $branch = 1;
        foreach ($lTree as $oTree) {
            $maxLevel = SPrepayrollUtils::findDepth($oTree);
            $oTree = SPrepayrollUtils::setLevelAndBranch($oTree, $branch, \SCons::PAY_W_S, $maxLevel);
            $branch++;
        }
    }

    private static function setLevelAndBranch($oTree, $branch, $payType, $level) {
        $lConfigs = SPrepayrollUtils::getPrepayrollConfigByGroup($oTree->id_group, $payType);
        foreach ($lConfigs as $oConfig) {
            $oConfig->order_vobo = $level;
            $oConfig->branch = $branch;
            
            $oPprConfig = PrepayReportConfig::find($oConfig->id_configuration);

            $oPprConfig->order_vobo = $level;
            $oPprConfig->branch = $branch;

            $oPprConfig->save();
        }
        $oTree->lConfigs = $lConfigs;

        $level--;
        foreach ($oTree->lGroups as $oSonGroup) {
            $oSonGroup = SPrepayrollUtils::setLevelAndBranch($oSonGroup, $branch, $payType, $level);
        }

        $oTree->branch = $branch;
        return $oTree;
    }

    public static function buildBranch($branch) : Collection {
        $oFatherGroup = DB::table('prepayroll_groups AS pg')
                            ->where('pg.is_delete', 0)
                            ->where('branch', $branch)
                            ->whereNull('pg.father_group_n_id')
                            ->select('pg.id_group')
                            ->orderBy('pg.group_name', 'ASC')
                            ->orderBy('pg.id_group', 'ASC')
                            ->first();
        
        if (is_null($oFatherGroup)) {
            return collect([]);
        }
        
        return SPrepayrollUtils::buildTree($oFatherGroup->id_group);
    }

    public static function buildTree($idGroup = null) : Collection {
        $lFathersGroups = DB::table('prepayroll_groups AS pg')
                        ->leftJoin('prepayroll_groups AS pgf', 'pg.father_group_n_id', '=', 'pgf.id_group')
                        ->where('pg.is_delete', 0);

        if (is_null($idGroup)) {
            $lFathersGroups = $lFathersGroups->whereNull('pg.father_group_n_id');
        }
        else {
            $lFathersGroups = $lFathersGroups->where('pg.id_group', $idGroup);
        }

        $lFathersGroups = $lFathersGroups->select('pg.id_group', 'pg.group_name', 'pg.father_group_n_id', 'pgf.group_name AS father_group_name')
                        ->orderBy('pg.group_name', 'ASC')
                        ->orderBy('pg.id_group', 'ASC')
                        ->get();

        foreach ($lFathersGroups as $fGroup) {
            $fGroup->level = 0;
            $fGroup = SPrepayrollUtils::getChildrenOfGroup($fGroup);
        }

        return $lFathersGroups;
    }

    private static function getChildrenOfGroup($oGroup) : Object {
        $aChildrens = SPrepayrollUtils::getChildren($oGroup->id_group);

        $lChildren = DB::table('prepayroll_groups AS pg')
                            ->leftJoin('prepayroll_groups AS pgf', 'pg.father_group_n_id', '=', 'pgf.id_group')
                            ->where('pg.is_delete', 0)
                            ->whereIn('pg.id_group', $aChildrens)
                            ->select('pg.id_group', 'pg.group_name', 'pg.father_group_n_id', 'pgf.group_name AS father_group_name')
                            ->orderBy('pg.father_group_n_id', 'ASC')
                            ->orderBy('pg.id_group', 'ASC')
                            ->get();

        $level = $oGroup->level + 1;
        foreach ($lChildren as $group) {
            $users = DB::table('prepayroll_groups_users AS pgu')
                        ->join('users AS u', 'pgu.head_user_id', '=', 'u.id')
                        ->where('group_id', $group->id_group)
                        ->select('u.*')
                        ->get();
        
            $group->head_users = $users;
            $group->level = $level;
            $group = SPrepayrollUtils::getChildrenOfGroup($group);
        }

        $oGroup->lGroups = $lChildren;

        return $oGroup;
    }

    private static function getPrepayrollConfigByGroup($idGroup, $payType) {
        $lCfgs = DB::table('prepayroll_report_configs AS prc')
                    ->where('prc.is_delete', false)
                    ->where('prc.group_n_id', $idGroup)
                    ->where($payType == \SCons::PAY_W_Q ? 'prc.is_biweek' : 'prc.is_week', true)
                    ->get();

        return $lCfgs;
    }
}
        