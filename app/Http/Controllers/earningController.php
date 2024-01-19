<?php

namespace App\Http\Controllers;

use App\Models\empVsPayroll;
use App\Models\earns_payroll;
use Illuminate\Http\Request;
use DB;

class earningController extends Controller
{
    public static function saveEarningFromJSON($lData,$idPayroll)
    {
        try {
        $resultado = false;
        $lData = json_decode($lData);
        foreach ($lData->response->empEar as $data) {
                $employee = DB::table("employees")->where("external_id",$data->id_emp)->first();
                $lPayroll = DB::table("emp_vs_payroll")->where("num_biweek", $idPayroll)->where("emp_id",$employee->id)->first();
                if (isset($lPayroll)) {
                    $id = $lPayroll->id_empvspayroll;
                    $resultado = true;
                }
                else {
                    $resultado = earningController::insertEarning($data,$idPayroll);
                }
            }
        }
        catch (\Throwable $th) {
            \Log::error($th->getMessage());
            \Log::error($employee->id);
            return $resultado;
        }
        return $resultado;
    }

    public static function updEarning($data,$id){
        return true;
    }

    public static function insertEarning($data,$idPayroll){
        try{
            DB::beginTransaction();
            $empVsPayroll = new empVsPayroll();
            $employee = DB::table('employees')
                        ->where('external_id', $data->id_emp)
                        ->first();
            $empVsPayroll->emp_id = $employee->id;
            $empVsPayroll->num_biweek = $idPayroll;
            $empVsPayroll->not_work = $data->day_not_work;
            $empVsPayroll->have_bonus = $data->have_bonus;
            $empVsPayroll->external_date_ini = $data->dt_ini;
            $empVsPayroll->external_date_end = $data->dt_fin;
            $empVsPayroll->save();
            foreach($data->earnings as $ear){
                $earnVsPayroll = new earns_payroll();
                $earnVsPayroll->empvspayroll_id = $empVsPayroll->id_empvspayroll;

                $earnings = DB::table('earnings')
                                    ->where('external_id',$ear->id_ear)
                                    ->first();

                $earnVsPayroll->ear_id = $earnings->id_ear;
                $earnVsPayroll->unt = $ear->unit_ear;
                $earnVsPayroll->save();
            }
            DB::commit();
        }catch(\Throwable $th) {
            DB::rollBack();
            \Log::error($th);
            return false;
        }
        return true;   
    }
}
