<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use App\SUtils\SDateUtils;
use App\Models\EmployeeVobo;
use App\Models\employees;

class EmployeeVobosController extends Controller
{
    public function processEmpVobo(Request $request)
    {

        $oEmployee = employees::where('num_employee', $request->num_employee)->first();
        $result = $this->haveVoboSupervisor($request->num_employee, $request->is_vobo, $request->start_date, $request->end_date, $oEmployee->way_pay_id);

        if(!$result[0] || $request->is_vobo){
    
            if ($oEmployee == null) {
                return response()->json(['success' => false, 'title' => 'Error', 'message' => 'No se encontró el empleado', 'icon' => 'error'], 500);
            }
    
            $startDate = $request->start_date;
            $endDate = $request->end_date;
    
            $oSDate = Carbon::parse($startDate);
            $number = SDateUtils::getNumberOfDate($startDate, $oEmployee->way_pay_id);
            $dates = SDateUtils::getDatesOfPayrollNumber($number[0], $number[1], $oEmployee->way_pay_id);
            // \DB::enableQueryLog();
            if ($dates[0] != $startDate || $dates[1] != $endDate) {
                return response()->json(['success' => false, 'title' => 'Error', 'message' => 'Las fechas no corresponen con el número de semana o quincena', 'icon' => 'error'], 500);
            }
            
            $qEmpVobo = EmployeeVobo::where('is_delete', false)
                                    ->where('employee_id', $oEmployee->id)
                                    ->where('year', $oSDate->year);
    
            if ($oEmployee->way_pay_id == \SCons::PAY_W_S) {
                $qEmpVobo = $qEmpVobo->where('is_week', true)
                                        ->where('num_week', $number[0]);
            }
            else {
                $qEmpVobo = $qEmpVobo->where('is_biweek', true)
                                        ->where('num_biweek', $number[0]);
            }
    
            $qEmpVobo = $qEmpVobo->first();
    
            if ($qEmpVobo == null) {
                if (! $request->is_vobo) {
                    return response()->json(['success' => false, 'title' => 'Error', 'message' => 'No se encontró el vobo', 'icon' => 'error'], 200);
                }
    
                $oEmpVobo = new EmployeeVobo();
                if ($oEmployee->way_pay_id == \SCons::PAY_W_S) {
                    $oEmpVobo->is_week = $oEmployee->way_pay_id == \SCons::PAY_W_S;
                    $oEmpVobo->num_week = $number[0];
                    $oEmpVobo->is_biweek = false;
                    $oEmpVobo->num_biweek = null;
                }
                else {
                    $oEmpVobo->is_week = false;
                    $oEmpVobo->num_week = null;
                    $oEmpVobo->is_biweek = $oEmployee->way_pay_id == \SCons::PAY_W_Q;
                    $oEmpVobo->num_biweek = $number[0];
                }
                $oEmpVobo->year = $number[1];
                $oEmpVobo->is_delete = false;
                $oEmpVobo->employee_id = $oEmployee->id;
                $oEmpVobo->vobo_by_id = auth()->user()->id;
                $oEmpVobo->dt_vobo = Carbon::now()->toDateTimeString();
                $oEmpVobo->deleted_by_id = null;
                $oEmpVobo->dt_deleted = null;
                $oEmpVobo->save();
    
                return response()->json(['success' => true, 'title' => 'Realizado', 'message' => 'Vobo creado', 'icon' => 'success'], 200);
            }
            else {
                if (! $request->is_vobo) {
                    $qEmpVobo->is_delete = true;
                    $qEmpVobo->deleted_by_id = auth()->user()->id;
                    $qEmpVobo->dt_deleted = Carbon::now()->toDateTimeString();
                    $qEmpVobo->save();
    
                    return response()->json(['success' => true, 'title' => 'Realizado', 'message' => 'Vobo eliminado', 'icon' => 'success'], 200);
                }
                else {
                    return response()->json(['success' => false, 'title' => 'Error', 'message' => 'Ya existe un vobo', 'icon' => 'error'], 200);
                }
            }
        }else{
            if(auth()->user()->id == $result[1]){
                return response()->json(['success' => false, 'title' => 'Error', 'message' => 'No se puede retirar el visto bueno porque ya diste el visto bueno de prenomina', 'icon' => 'error'], 200);
            }
            $headUser = \DB::table('users')
                            ->where('id', $result[1])
                            ->value('name');
            return response()->json(['success' => false, 'title' => 'Error', 'message' => 'No se puede retirar el visto bueno porque '.$headUser.' dio visto bueno de prenomina', 'icon' => 'error'], 200);
        }

    }

    public function haveVoboSupervisor($Employee_num, $is_vobo, $start_date, $end_date, $pay_way){
        $startDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date);

        $numWeek = 0;
        $numBiWeek = 0;
        if($pay_way == \SCons::PAY_W_S){
            $numWeek = \DB::table('week_cut')
                            ->where([['ini',$start_date],['fin',$end_date]])
                            ->value('num');
        } else if ($pay_way == \SCons::PAY_W_Q){
            $numBiWeek = \DB::table('hrs_prepay_cut')
                            ->where('dt_cut',$end_date)
                            ->value('num');
        }

        $headUsers = \DB::table('employees as e')
                        ->join('prepayroll_group_employees as pge', 'pge.employee_id', '=', 'e.id')
                        ->join('prepayroll_groups_users as pgu', 'pgu.group_id', '=', 'pge.group_id')
                        ->where('e.num_employee',$Employee_num)
                        ->pluck('pgu.head_user_id')
                        ->toArray();

        if(!$is_vobo){
            if($pay_way == \SCons::PAY_W_S){
                $haveVobo = \DB::table('prepayroll_report_auth_controls as prac')
                                ->select('is_vobo', 'user_vobo_id')
                                ->whereIn('prac.user_vobo_id', $headUsers)
                                ->where([['year',$endDate->format('Y')],['num_week',$numWeek]])
                                ->get();
            }else if($pay_way == \SCons::PAY_W_Q){
                $haveVobo = \DB::table('prepayroll_report_auth_controls as prac')
                                ->select('is_vobo', 'user_vobo_id')
                                ->whereIn('prac.user_vobo_id', $headUsers)
                                ->where([['year',$endDate->format('Y')],['num_biweek',$numBiWeek]])
                                ->get();
            }

            foreach ($haveVobo as $vobo) {
                if($vobo->is_vobo){
                    return [true, $vobo->user_vobo_id];
                }
            }
            return [false, null];
        }else{
            return [false, null];
        }
    }
}
