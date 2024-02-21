<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class apiGlobalUsersController extends Controller
{
    public static function getUser($full_name, $external_id, $employee_num){
        $query = User::join('employees as e', 'e.id', '=', 'users.employee_id')
                    ->where('e.is_active', 1)
                    ->where('users.is_delete', 0);

        if(!is_null($full_name)){
            $query = $query->where('e.name', $full_name);
        }
        if(!is_null($external_id)){
            $query = $query->where('e.external_id', $external_id);
        }
        if(!is_null($employee_num)){
            $query = $query->where('e.num_employee', $employee_num);
        }
        
        $query = $query->select(
            'users.id',
            'e.name',
            'e.external_id',
            'e.num_employee'
            )->get();

        return $query;
    }

    public function getUserToGlobalUser(Request $request){
        try {
            $full_name = $request->full_name;
            $external_id = $request->external_id;
            $employee_num = $request->employee_num;
            $user = null;

            $query = self::getUser($full_name, $external_id, $employee_num);
            
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }

        if($query->count() == 1){
            $user = $query->first();
            return response()->json([
                'status' => 'success',
                'message' => "Se encontró el usuario correctamente",
                'data' => $user
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }else if($query->count() == 0){
            return response()->json([
                'status' => 'success',
                'message' => "No se encontró el usuario: " . $full_name . " " . $external_id . " " . $employee_num . " " . " , por favor verifique los datos ingresados. ",
                'data' => null
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }else if($query->count() > 0){
            return response()->json([
                'status' => 'error',
                'message' => 'Multiple users found for ' . $full_name . ' ' . $external_id . ' ' . $employee_num ,
                'data' => null
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getListUsersToGlobalUsers(Request $request){
        try {
            $lUsers =  json_decode($request->lUsers);
            $lUsersResponse = [];
            foreach ($lUsers as $user) {
                $query = self::getUser($user->full_name, $user->external_id, $user->employee_num);

                if($query->count() == 1){
                    $user = $query->first();
                    $lUsersResponse[] = [
                        'status' => 'success',
                        'message' => "Se encontró el usuario correctamente",
                        'user' => $user
                    ];
                }else if($query->count() == 0){
                    $lUsersResponse[] = [
                        'status' => 'success',
                        'message' => "No se encontró el usuario: " . $user->full_name . " " . $user->external_id . " " . $user->employee_num . " " . " , por favor verifique los datos ingresados. ",
                        'user' => null
                    ];
                }else if($query->count() > 0){
                    $lUsersResponse[] = [
                        'status' => 'error',
                        'message' => 'Multiple users found for ' . $user->full_name . ' ' . $user->external_id . ' ' . $user->employee_num ,
                        'user' => null
                    ];
                }
            }
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Se encontrarón los usuarios correctamente",
            'data' => $lUsersResponse
            ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function updateGlobalUser(Request $request){
        try {
            $Users =  json_decode($request->user);
            $us = (object)$Users;
            \DB::beginTransaction();
            $user = User::find($us->user_system_id);
            $user->name = $us->username;
            $user->email = isset($us->institutional_mail) ? $us->institutional_mail : $us->email;
            $user->password = $us->pass;
            $user->updated_by = 15;
            $user->save();        
            \DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => "Se actualizo el usuario con exito",
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }catch(\Throwable $th){
            \DB::rollBack();
            \Log::error($th);
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
    public static function getUserById($userId){
        try {
            $oUser = User::findOrFail($userId);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'data' => null
                ], 500, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Se encontró el usuario correctamente",
            'data' => $oUser
            ], 200, [], JSON_UNESCAPED_UNICODE);
    }
    
}
