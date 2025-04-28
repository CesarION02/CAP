<?php namespace App\SUtils;
use DB;

class SAssignGroups {
    public static function assignGroup($users, $groupDept, $groupPrepayroll){
        foreach($users AS $user){
            DB::beginTransaction();
            try{
                if (!empty($groupDept)) {
                    $group_dept = DB::table('group_dept_user')
                        ->where('user_id',$user)
                        ->get();

                    $idsActuales = $group_dept->pluck('groupdept_id')->toArray();
                    $idsSistema  = $group_dept->where('is_system', 1)->pluck('groupdept_id')->toArray();

                    // IDs que hay que eliminar: están en BD pero no vienen en la solicitud y son del sistema
                    $toDelete = array_diff($idsSistema, $groupDept);

                    if (!empty($toDelete)) {
                        DB::table('group_dept_user')
                            ->where('user_id', $user)
                            ->whereIn('groupdept_id', $toDelete)
                            ->delete();
                    }

                    // IDs que hay que insertar: vienen nuevos pero no están en BD
                    $toInsert = array_diff($groupDept, $idsActuales);

                    foreach ($toInsert as $groupId) {
                        DB::table('group_dept_user')->insert([
                            'user_id'     => $user,
                            'groupdept_id'=> $groupId,
                            'created_by'  => auth()->id(),
                            'updated_by'  => auth()->id(),
                            'is_delete'   => 0,
                            'is_system'   => 1,
                            'created_at'  => now(),
                            'updated_at'  => now()
                        ]);
                    }
                }   
                if (!empty($groupPrepayroll)) { 
                    $group_prepayroll = DB::table('prepayroll_groups_users')
                        ->where('head_user_id',$user)
                        ->get();
                    
                    $idsActuales = $group_prepayroll->pluck('group_id')->toArray();
                    $idsSistema  = $group_prepayroll->where('is_system', 1)->pluck('group_id')->toArray();

                    // IDs que hay que eliminar: están en BD pero no vienen en la solicitud y son del sistema
                    $toDelete = array_diff($idsSistema, $groupPrepayroll);

                    if (!empty($toDelete)) {
                        DB::table('prepayroll_groups_users')
                            ->where('head_user_id', $user)
                            ->whereIn('group_id', $toDelete)
                            ->delete();
                    }

                    // IDs que hay que insertar: vienen nuevos pero no están en BD
                    $toInsert = array_diff($groupPrepayroll, $idsActuales);

                    foreach ($toInsert as $groupId) {
                        DB::table('prepayroll_groups_users')->insert([
                            'head_user_id' => $user,
                            'group_id' => $groupId,
                            'user_by_id'  => auth()->id(),
                            'is_system'   => 1,
                            'created_at'  => now(),
                            'updated_at'  => now()
                        ]);
                    }
                }

                DB::commit();
            }
            catch(\Exception $e){
                DB::rollBack();
                // Aquí puedes loggear si quieres
                \Log::error("Error al asignar grupos al usuario $user: " . $e->getMessage());
                }
        }      
    }

    public static function newAssignGroup($user){
        DB::beginTransaction();
        try {
            $job = DB::table('users')
                ->join('employees','users.employee_id','=','employees.id')
                ->select('employees.job_rh_id AS job_rh')
                ->where('users.id', $user)
                ->get();
            
            $group_dept = DB::table('position_grp_dept')
                ->where('job_rh_id', $job[0]->job_rh)
                ->get();

            foreach ($group_dept as $groupId) {
                DB::table('group_dept_user')->insert([
                    'user_id'     => $user,
                    'groupdept_id'=> $groupId->grp_dept_id,
                    'created_by'  => auth()->id(),
                    'updated_by'  => auth()->id(),
                    'is_delete'   => 0,
                    'is_system'   => 1,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ]);
            }
            
            $group_prepayroll = DB::table('position_prepayroll')
                ->where('job_rh_id', $job[0]->job_rh)
                ->get();
            
            foreach ($group_prepayroll as $groupId) {
                DB::table('prepayroll_groups_users')->insert([
                    'head_user_id' => $user,
                    'group_id' => $groupId->grp_prepayroll_id,
                    'user_by_id'  => auth()->id(),
                    'is_system'   => 1,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("Error al asignar nuevos grupos a usuario $user: " . $e->getMessage());
        }
    }
}