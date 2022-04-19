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

        if ($oEmployee == null) {
            return response()->json(['message' => 'No se encontró el empleado'], 500);
        }

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $oSDate = Carbon::parse($startDate);
        $number = SDateUtils::getNumberOfDate($startDate, $oEmployee->way_pay_id);
        $dates = SDateUtils::getDatesOfPayrollNumber($number, $oSDate->year, $oEmployee->way_pay_id);
        // \DB::enableQueryLog();
        if ($dates[0] != $startDate || $dates[1] != $endDate) {
            return response()->json(['message' => 'Las fechas no corresponen con el número de semana o quincena'], 500);
        }
        
        $qEmpVobo = EmployeeVobo::where('is_delete', false)
                                ->where('employee_id', $oEmployee->id)
                                ->where('year', $oSDate->year);

        if ($oEmployee->way_pay_id == \SCons::PAY_W_S) {
            $qEmpVobo = $qEmpVobo->where('is_week', true)
                                    ->where('num_week', $number);
        }
        else {
            $qEmpVobo = $qEmpVobo->where('is_biweek', true)
                                    ->where('num_biweek', $number);
        }

        $qEmpVobo = $qEmpVobo->first();

        if ($qEmpVobo == null) {
            if (! $request->is_vobo) {
                return response()->json(['message' => 'No se encontró el vobo'], 200);
            }

            $oEmpVobo = new EmployeeVobo();
            if ($oEmployee->way_pay_id == \SCons::PAY_W_S) {
                $oEmpVobo->is_week = $oEmployee->way_pay_id == \SCons::PAY_W_S;
                $oEmpVobo->num_week = $number;
                $oEmpVobo->is_biweek = false;
                $oEmpVobo->num_biweek = null;
            }
            else {
                $oEmpVobo->is_week = false;
                $oEmpVobo->num_week = null;
                $oEmpVobo->is_biweek = $oEmployee->way_pay_id == \SCons::PAY_W_Q;
                $oEmpVobo->num_biweek = $number;
            }
            $oEmpVobo->year = $oSDate->year;
            $oEmpVobo->is_delete = false;
            $oEmpVobo->employee_id = $oEmployee->id;
            $oEmpVobo->vobo_by_id = auth()->user()->id;
            $oEmpVobo->dt_vobo = Carbon::now()->toDateTimeString();
            $oEmpVobo->deleted_by_id = null;
            $oEmpVobo->dt_deleted = null;
            $oEmpVobo->save();

            return response()->json(['message' => 'Vobo creado'], 200);
        }
        else {
            if (! $request->is_vobo) {
                $qEmpVobo->is_delete = true;
                $qEmpVobo->deleted_by_id = auth()->user()->id;
                $qEmpVobo->dt_deleted = Carbon::now()->toDateTimeString();
                $qEmpVobo->save();

                return response()->json(['message' => 'Vobo eliminado'], 200);
            }
            else {
                return response()->json(['message' => 'Ya existe un vobo'], 200);
            }
        }
    }
}
