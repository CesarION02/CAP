<?php namespace App\SUtils;

use App\SUtils\SPayrollDelegationUtils;
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
                                ->where('pge.is_delete',0)
                                ->where('e.is_delete',0)
                                ->where('e.is_active',1)
                                ->where('e.way_pay_id', $payType)
                                ->pluck('e.id');

            $lDeptEmployees = \DB::table('prepayroll_group_deptos as pgd')
                                ->join('employees as e', 'e.department_id', '=', 'pgd.department_id')
                                ->where('e.is_delete',0)
                                ->where('e.is_active',1)
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
     * @return void
     */
    private static function getChildrenOfGroups($groups) {
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
     * @return void
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

    public static function isAllEmployeesOk($idUser, $idVobo)
    {
        $bDirectEmployees = true;
        $prepayroll = \DB::table('prepayroll_report_auth_controls AS prac')
                            ->where('prac.user_vobo_id', $idUser)
                            ->where('prac.id_control', $idVobo)
                            ->first();

        if ($prepayroll == null) {
            return false;
        }

        if (! env('VOBO_BY_EMP_ENABLED')) {
            return true;
        }
        
        $payType = $prepayroll->is_week ? \SCons::PAY_W_S : \SCons::PAY_W_Q;
        $number = $prepayroll->is_week ? $prepayroll->num_week : $prepayroll->num_biweek;
        
        $iDelegation = null;
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
                                    ->pluck('empvb.employee_id')
                                    ->toArray();

        $aEmployeesNotOk = array_diff($aEmployees, $aEmployeesOk);

        if (count($aEmployeesNotOk) > 0) {
            return false;
        }

        return true;
    }
}
        