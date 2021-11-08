<?php namespace App\SUtils;

class SPermissions {

    public static function getPermissionsOfUser($idUser)
    {
        $permissions = \DB::table('rol AS r')
                        ->join('permiso_rol AS pr AS r', 'r.id', '=', 'pr.rol_id')
                        ->join('permission AS p', 'pr.permission_id', '=', 'p.id')
                        ->join('user_rol AS ur', 'r.id', '=', 'ur.rol_id')
                        ->where('ur.user_id', $idUser)
                        ->select('p.*')
                        ->distinct()
                        ->get();
        
        return $permissions;
    }

    public static function hasPermission($idUser, $permissionName)
    {
        $permissions = \DB::table('rol AS r')
                        ->join('permiso_rol AS pr AS r', 'r.id', '=', 'pr.rol_id')
                        ->join('permission AS p', 'pr.permission_id', '=', 'p.id')
                        ->join('user_rol AS ur', 'r.id', '=', 'ur.rol_id')
                        ->where('ur.user_id', $idUser)
                        ->where('p.name', $permissionName)
                        ->select('p.id')
                        ->get();
        
        return count($permissions) > 0;
    }

    public static function usersWithRol($rolName)
    {
        $users = \DB::table('rol AS r')
                        ->join('permiso_rol AS pr AS r', 'r.id', '=', 'pr.rol_id')
                        ->join('permission AS p', 'pr.permission_id', '=', 'p.id')
                        ->join('user_rol AS ur', 'r.id', '=', 'ur.rol_id')
                        ->join('users AS u', 'ur.user_id', '=', 'u.id')
                        ->where('r.name', $rolName)
                        ->where('u.is_delete', 0)
                        ->select('u.*')
                        ->get();
        
        return $users;
    }
}