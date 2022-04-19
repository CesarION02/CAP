<?php namespace App\SUtils;

class SPrepayrollUtils {

    /**
     * Obtiene los empleados correspondientes al grupo asignado al usuario recibido y a los grupos
     * dependientes del mismo.
     *
     * @param int $idUser
     * @return void
     */
    public static function getEmployeesByUser($idUser) {
        $roles = \Auth::user()->roles;
        $config = \App\SUtils\SConfiguration::getConfigurations(); // Obtengo las configuraciones del sistema

        $seeAll = false;
        foreach ($roles as $rol) {
            if (in_array($rol->id, $config->rolesCanSeeAll)) {
                $seeAll = true;
                break;
            }
        }

        if ($seeAll) {
            return null;
        }

        // Obtiene los grupos de prenómina que el usuario puede ver
        $groups = \DB::table('prepayroll_groups_users AS pgu')
                            ->where('pgu.head_user_id', $idUser)
                            ->pluck('pgu.group_id')
                            ->toArray();

        // Obtiene los sub-grupos de los grupos directos
        $lGroups = SPrepayrollUtils::getChildrenOfGroups($groups);

        // Obtiene los empleados pertenecientes a todos los grupos que el usuario puede ver
        $aEmployees = \DB::table('prepayroll_groups AS pg')
                        ->join('prepayroll_group_employees AS pge', 'pg.id_group', '=', 'pge.group_id')
                        ->whereIn('pg.id_group', $lGroups)
                        ->pluck('pge.employee_id')
                        ->toArray();

        // Obtiene los empleados pertenecientes a los grupos que el usuario no puede ver
        $aEmployeesOthers = \DB::table('prepayroll_groups AS pg')
                            ->join('prepayroll_group_employees AS pge', 'pg.id_group', '=', 'pge.group_id')
                            ->whereNotIn('pg.id_group', $lGroups)
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
                                ->whereIn('d.id', $deptsOfPPGroups)
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
}
        