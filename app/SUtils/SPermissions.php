<?php namespace App\SUtils;

use App\Models\User;
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

        $directPermission = \DB::table('user_permissions AS up')
                                ->join('permission AS p', 'up.permission_id', '=', 'p.id')
                                ->where('up.user_id', $idUser)
                                ->select('p.*')
                                ->distinct()
                                ->get();

        $permissions = array_merge($permissions, $directPermission);

        return array_unique($permissions);
    }

    public static function hasPermission($idUser, $permissionName)
    {
        $hasPermission = \DB::table('rol AS r')
                        ->join('permiso_rol AS pr AS r', 'r.id', '=', 'pr.rol_id')
                        ->join('permission AS p', 'pr.permission_id', '=', 'p.id')
                        ->join('user_rol AS ur', 'r.id', '=', 'ur.rol_id')
                        ->where('ur.user_id', $idUser)
                        ->where('p.name', $permissionName)
                        ->exists();

        if ($hasPermission) {
            return true;
        }

        $hasPermission = \DB::table('user_permissions AS up')
                        ->join('permission AS p', 'up.permission_id', '=', 'p.id')
                        ->where('up.user_id', $idUser)
                        ->where('p.name', $permissionName)
                        ->exists();

        return $hasPermission;
    }

    public static function hasPermissionById($idUser, $idPermission)
    {
        $hasPermission = \DB::table('rol AS r')
                        ->join('permiso_rol AS pr AS r', 'r.id', '=', 'pr.rol_id')
                        ->join('permission AS p', 'pr.permission_id', '=', 'p.id')
                        ->join('user_rol AS ur', 'r.id', '=', 'ur.rol_id')
                        ->where('ur.user_id', $idUser)
                        ->where('p.id', $idPermission)
                        ->exists();
        
        if ($hasPermission) {
            return true;
        }

        $hasPermission = \DB::table('user_permissions AS up')
                                ->where('up.user_id', $idUser)
                                ->where('up.permission_id', $idPermission)
                                ->exists();
        
        return $hasPermission;
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

    public static function userHasRole($idUser, $idRole)
    {
        return \DB::table('user_rol')
                    ->where('user_id', $idUser)
                    ->where('rol_id', $idRole)
                    ->exists();
    }

    public static function assignMenuByDefaultRol($idUser, $idMenu)
    {
        $oUser = User::find($idUser);
        if ($oUser == null) {
            return null;
        }

        $roles = $oUser->roles->pluck('id')->toArray();
            
        $hasMenu = \DB::table('menu_rol')
                            ->whereIn('rol_id', $roles)
                            ->where('menu_id', $idMenu)
                            ->exists();
        
        if (! $hasMenu) {
            $config = \App\SUtils\SConfiguration::getConfigurations(); // Obtengo las configuraciones del sistema
            $role = $config->idRoleDefault; // Obtengo el rol por defecto
            $id = \DB::table('user_rol')->insertGetId([
                'state' => 1,
                'rol_id' => $role,
                'user_id' => $idUser,
                'created_at' => now(), 
                'updated_at' => now()
            ]);

            return $id;
        }

        return 0;
    }
}