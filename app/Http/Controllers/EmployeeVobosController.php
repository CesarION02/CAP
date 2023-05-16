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
        try {
            $oEmployee = employees::where('num_employee', $request->num_employee)->first();
            $hasVobo = $this->haveVoboSupervisor($request->num_employee, $request->is_vobo, $request->start_date, $request->end_date, $oEmployee->way_pay_id);

            if (!$hasVobo[0] || $request->is_vobo) {
                if (is_null($oEmployee)) {
                    return response()->json(['success' => false, 'title' => 'Error', 'message' => 'No se encontró el empleado', 'icon' => 'error'], 500);
                }

                $startDate = $request->start_date;
                $endDate = $request->end_date;
                //bug fix porque no encuentra el visto bueno porque agarra año anterior
                //$oSDate = Carbon::parse($startDate);
                $oSDate = Carbon::parse($endDate);
                //
                $number = SDateUtils::getNumberOfDate($startDate, $oEmployee->way_pay_id);
                $dates = SDateUtils::getDatesOfPayrollNumber($number[0], $number[1], $oEmployee->way_pay_id);
                // \DB::enableQueryLog();
                if ($dates[0] != $startDate || $dates[1] != $endDate) {
                    return response()->json(['success' => false, 'title' => 'Error', 'message' => 'Las fechas no corresponen con el número de semana o quincena', 'icon' => 'error'], 500);
                }

                $qEmpVobo = EmployeeVobo::where('is_delete', 0)
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
                if (is_null($qEmpVobo)) {
                    // if (!$request->is_vobo) {
                    //     return response()->json(['success' => false, 'title' => 'Error', 'message' => 'No se encontró el vobo', 'icon' => 'error'], 200);
                    // }

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
                    $oEmpVobo->deleted_by_id = null;
                    $oEmpVobo->dt_deleted = null;

                    if ($request->is_vobo) {
                        $oEmpVobo->is_vobo = true;
                        $oEmpVobo->comments = "";
                        $oEmpVobo->vobo_by_id = auth()->user()->id;
                        $oEmpVobo->dt_vobo = Carbon::now()->toDateTimeString();
                        $oEmpVobo->is_rejected = false;
                        $oEmpVobo->rejected_by_id = null;
                        $oEmpVobo->dt_rejected = null;
                    }
                    else {
                        $oEmpVobo->is_vobo = false;
                        $oEmpVobo->vobo_by_id = null;
                        $oEmpVobo->dt_vobo = null;
                        $oEmpVobo->is_rejected = true;
                        $oEmpVobo->comments = !isset($request->comments) || is_null($request->comments) ? "" : $request->comments;
                        $oEmpVobo->rejected_by_id = auth()->user()->id;
                        $oEmpVobo->dt_rejected = Carbon::now()->toDateTimeString();
                    }

                    $oEmpVobo->save();
                }
                else {
                    if (($request->is_reject && $qEmpVobo->is_rejected) || ($request->is_vobo && $qEmpVobo->is_vobo)) {
                        return response()->json(['success' => false, 'title' => 'Error', 'message' => 'Ya existe este vobo', 'icon' => 'error'], 200);
                    }
                    else {
                        if ($request->is_reject) {
                            $qEmpVobo->is_vobo = false;
                            $qEmpVobo->vobo_by_id = null;
                            $qEmpVobo->dt_vobo = null;
                            $qEmpVobo->is_rejected = true;
                            $qEmpVobo->comments = !isset($request->comments) || is_null($request->comments) ? "" : $request->comments;
                            $qEmpVobo->rejected_by_id = auth()->user()->id;
                            $qEmpVobo->dt_rejected = Carbon::now()->toDateTimeString();
                        }
                        else {
                            $qEmpVobo->is_vobo = true;
                            $qEmpVobo->comments = "";
                            $qEmpVobo->vobo_by_id = auth()->user()->id;
                            $qEmpVobo->dt_vobo = Carbon::now()->toDateTimeString();
                            $qEmpVobo->is_rejected = false;
                            $qEmpVobo->rejected_by_id = null;
                            $qEmpVobo->dt_rejected = null;
                        }
                        $qEmpVobo->save();
                    }
                }

                $lEmpVobos = \DB::table('prepayroll_report_emp_vobos AS evb')
                                            ->leftJoin('users AS u', 'evb.vobo_by_id', '=', 'u.id')
                                            ->leftJoin('users AS ur', 'evb.rejected_by_id', '=', 'ur.id')
                                            ->join('employees AS e', 'evb.employee_id', '=', 'e.id')
                                            ->where('evb.is_delete', 0)
                                            ->where('year', $number[1])
                                            ->select('u.name AS user_vobo_name',
                                                    'ur.name AS user_rejected_name',
                                                    'evb.employee_id', 
                                                    'evb.vobo_by_id', 
                                                    'evb.is_vobo',
                                                    'evb.is_rejected',
                                                    'evb.comments',
                                                    'e.num_employee');

                if ($oEmployee->way_pay_id == \SCons::PAY_W_Q) {
                    $lEmpVobos = $lEmpVobos->where('evb.is_biweek', true)
                                            ->where('evb.num_biweek', $number[0]);
                }
                else {
                    $lEmpVobos = $lEmpVobos->where('evb.is_week', true)
                                            ->where('evb.num_week', $number[0]);
                }

                $lEmpVobos = $lEmpVobos->get()->keyBy('num_employee')->toArray();

                $text = $request->is_reject ? 'rechazado' : 'creado';
                $resp = "Vobo $text correctamente";

                return response()->json(['success' => true, 'title' => 'Realizado', 'message' => $resp, 'icon' => 'success', 'lvobos' => $lEmpVobos], 200);
            }
            else {
                if (auth()->user()->id == $hasVobo[1]) {
                    return response()->json(['success' => false, 'title' => 'Error', 'message' => 'No se puede retirar el visto bueno porque ya diste el visto bueno de prenomina', 'icon' => 'error'], 200);
                }

                $headUser = \DB::table('users')
                            ->where('id', $hasVobo[1])
                            ->value('name');

                return response()->json(['success' => false, 'title' => 'Error', 'message' => 'No se puede retirar el visto bueno porque ' . $headUser . ' dio visto bueno de prenomina', 'icon' => 'error'], 200);
            }
        }
        catch (\Throwable $th) {
            \Log::error($th);

            return response()->json(['success' => false, 'title' => 'Error', 'message' => $th->getMessage(), 'icon' => 'error'], 200);
        }
    }

    /**
     * Verifica si el supervisor ya dio visto bueno a la prenomina
     *
     * @param int $Employee_num
     * @param boolean $is_vobo
     * @param string $start_date formato Y-m-d
     * @param string $end_date formato Y-m-d
     * @param int $pay_way 1: quincenal, 2: semanal
     * 
     * @return array [con vobo, id de usuario]
     */
    public function haveVoboSupervisor($Employee_num, $is_vobo, $start_date, $end_date, $pay_way) {
        $startDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date);

        $numWeek = 0;
        $numBiWeek = 0;
        if ($pay_way == \SCons::PAY_W_S) {
            $numWeek = \DB::table('week_cut')
                ->where([['ini', $start_date], ['fin', $end_date]])
                ->value('num');
        }
        else if ($pay_way == \SCons::PAY_W_Q) {
            $numBiWeek = \DB::table('hrs_prepay_cut')
                ->where('dt_cut', $end_date)
                ->value('num');
        }

        $headUsers = \DB::table('employees as e')
            ->join('prepayroll_group_employees as pge', 'pge.employee_id', '=', 'e.id')
            ->join('prepayroll_groups_users as pgu', 'pgu.group_id', '=', 'pge.group_id')
            ->where('e.num_employee', $Employee_num)
            ->pluck('pgu.head_user_id')
            ->toArray();

        if (!$is_vobo) {
            if ($pay_way == \SCons::PAY_W_S) {
                $haveVobo = \DB::table('prepayroll_report_auth_controls as prac')
                    ->select('is_vobo', 'user_vobo_id')
                    ->whereIn('prac.user_vobo_id', $headUsers)
                    ->where([['year', $endDate->format('Y')], ['num_week', $numWeek]])
                    ->get();
            }
            else if ($pay_way == \SCons::PAY_W_Q) {
                $haveVobo = \DB::table('prepayroll_report_auth_controls as prac')
                    ->select('is_vobo', 'user_vobo_id')
                    ->whereIn('prac.user_vobo_id', $headUsers)
                    ->where([['year', $endDate->format('Y')], ['num_biweek', $numBiWeek]])
                    ->get();
            }

            foreach ($haveVobo as $vobo) {
                if ($vobo->is_vobo) {
                    return [true, $vobo->user_vobo_id];
                }
            }
            return [false, null];
        }
        else {
            return [false, null];
        }
    }
}
