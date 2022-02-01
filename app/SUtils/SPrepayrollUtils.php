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
        $groups = \DB::table('prepayroll_groups AS pg')
                            ->where('pg.head_user_id', $idUser)
                            ->pluck('pg.id_group')
                            ->toArray();

        $lGroups = SPrepayrollUtils::getChildrenOfGroups($groups);

        $eSubs = \DB::table('prepayroll_groups AS pg')
                        ->join('prepayroll_group_employees AS pge', 'pg.id_group', '=', 'pge.group_id')
                        ->whereIn('pg.id_group', $lGroups)
                        ->pluck('pge.employee_id')
                        ->toArray();

        return $eSubs;
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
                        ->where('pg.head_user_id', $idUser)
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
}
        