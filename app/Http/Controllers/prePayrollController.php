<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Validator;
use Carbon\Carbon;
use App\SUtils\SInfoWithPolicy;
use App\Models\employees;
use App\Models\incident;
use App\Models\prepayroll_control;
use App\Models\cutCalendarQ;
use App\SPayroll\SPrePayroll;
use App\SPayroll\SPrePayrollRow;
use App\SPayroll\SPrePayrollDay;
use App\Models\prepayrollchange;
use App\Models\prepayrollAuthControl;
use App\Models\PrepayReportControl;
use App\Models\prepayrollVoboSkipped;
use App\Http\Controllers\PrepayrollReportController;
use App\Http\Controllers\SyncController;
use App\SUtils\SPrepayrollUtils;
use Illuminate\Database\QueryException;
class prePayrollController extends Controller
{
    public function __construct() {
        // Códigos de respuesta:
        $this->OK = "200";
        $this->ERROR = "500"; //  Error del servidor
        $this->NOT_VOBO = "550"; // Nómina no autorizada
    }
    
    /**
     * Retorna un JSON con la información de la prenómina
     *
     * @param Request $request
     * 
     * @return JSON string 
     * {
     *	start_date: date,
     *	end_date: date,
     *	rows: [
     *			{
     *			employee_id: integer,
     *          double_overtime: decimal,
     *          triple_overtime: decimal,
     *			days: [
     *				{
     *				dt_date: date,
     *              num_absences: int,
     *              is_sunday: boolean,
     *              is_day_off: boolean,
     *              events: []
     *				holiday_id: integer
     *				}
     *				]
     *			},
     *		]
     * }
     */
    public function getPrePayroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
            'employees' => 'required',
            'pay_type' => 'required',
            'data_type' => 'required'
        ]);

        if (! $validator->passes()) {
            // return response()->json(['success'=>'Added new records.']);
            return response()->json(['error' => $validator->errors()->all()]);
        }

        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $aEmployeeIds = $request->employees;
            $payType = $request->pay_type == "1" ? \SCons::PAY_W_S : \SCons::PAY_W_Q;
            $dataType = $request->data_type;

            if (is_string($aEmployeeIds)) {
                $aEmployeeIds = explode(",", $aEmployeeIds);
            }

            if (! PrepayrollReportController::isFreeVoboPrepayroll($startDate, $payType)) {
                $response = new \stdClass();
                $response->code = $this->NOT_VOBO;
                $response->message = "La prenómina no se ha autorizado para la fecha: ".$startDate;

                return response()->json($response);
            }
        
            $oPrepayroll = $this->makePrepayroll($startDate, $endDate, $aEmployeeIds, $payType, $dataType);
        }
        catch (\Throwable $e) {
            $response = (object) [
                "code" => $this->ERROR,
                "message" => $e->getMessage()
            ];

            return json_encode($response, JSON_PRETTY_PRINT);
        }

        $response = (object) [
                        "code" => $this->OK,
                        "message" => "OK",
                        "prepayrollData" => $oPrepayroll
                    ];

        return json_encode($response, JSON_PRETTY_PRINT);
    }

    /**
     * Obtiene la prenómina consultando días entre fechas, empleados, horarios y checadas
     *
     * @param String $startDate
     * @param String $endDate
     * @param array $aEmployeeIds
     * 
     * @return SPrePayroll object
     */
    private function makePrepayroll($startDate, $endDate, $aEmployeeIds, $payType, $dataType)
    {
        $aDates = [];
        $oStartDate = Carbon::parse($startDate);
        $oEndDate = Carbon::parse($endDate);
        $oDate = clone $oStartDate;

        /**
         * crea un arreglo con los días a consultar
         */
        while ($oDate->lessThanOrEqualTo($oEndDate)) {
            $aDates[] = $oDate->toDateString();
            $oDate->addDay();
        }

        /**
         * obtiene el número de empleado de cap por medio del id externo
         */
        $lEmployees = employees::whereIn('external_id', $aEmployeeIds)
                                    ->pluck('external_id', 'id');

        $lCapEmployees = employees::whereIn('external_id', $aEmployeeIds)
                                    ->pluck('id');

        $config = \App\SUtils\SConfiguration::getConfigurations();

        $correcto = SyncController::syncronizeWithERP($config->lastSyncDateTime);
        /**
         * Obtiene el reporte de horas extra, que contiene también domingos y festivos.
         */
        $info = SInfoWithPolicy::preProcessInfo($startDate, $oStartDate->year, $endDate, $payType);
        $lExtras = \DB::table('processed_data')
                                ->join('employees','employees.id','=','processed_data.employee_id')
                                ->whereIn('employees.id', $lCapEmployees)
                                ->whereBetween('outDate',[$startDate, $endDate])
                                ->get();

        $prePayroll = new SPrePayroll();
        $prePayroll->start_date = $startDate;
        $prePayroll->end_date = $endDate;
        $prePayroll->rows = [];

        foreach ($lEmployees as $idEmployee => $extId) {
            $row = new SPrePayrollRow();
            $row->employee_id = $extId;
            $row->days = [];

            /**
             * Determinar horas extras dobles y triples
             */
            $lCExtrasEmp = clone collect($lExtras);
            $lCExtrasEmp = $lCExtrasEmp->where('employee_id', $idEmployee);
            $lGrouped = $lCExtrasEmp->groupBy('employee_id')->map(function ($row) {
                                                $registry = (object) [
                                                    'minsExtraDouble' => $row->sum('extraDobleMins'),
                                                    'minsExtraTriple' => $row->sum('extraTripleMins'),
                                                    'minsExtraDoubleNoOficial' => $row->sum('extraDobleMinsNoficial'),
                                                    'minsExtraTripleNoOficial' => $row->sum('extraTripleMinsNoficial'),
                                                ];
        
                                        return $registry;
                                    });
                                    
            if (sizeof($lGrouped) > 0) {
                switch ($dataType) {
                    case \SCons::LIMITED_DATA:
                        $row->double_overtime = $lGrouped[$idEmployee]->minsExtraDouble / 60;
                        $row->triple_overtime = $lGrouped[$idEmployee]->minsExtraTriple / 60;
                        break;
                    case \SCons::OTHER_DATA:
                        $row->double_overtime = $lGrouped[$idEmployee]->minsExtraDoubleNoOficial / 60;
                        $row->triple_overtime = $lGrouped[$idEmployee]->minsExtraTripleNoOficial / 60;
                        break;
    
                    case \SCons::ALL_DATA:
                        $row->double_overtime = ($lGrouped[$idEmployee]->minsExtraDouble + 
                                                    $lGrouped[$idEmployee]->minsExtraDoubleNoOficial) / 60;
                        $row->triple_overtime = ($lGrouped[$idEmployee]->minsExtraTriple +
                                                    $lGrouped[$idEmployee]->minsExtraTripleNoOficial) / 60;
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }

            foreach ($aDates as $sDate) {
                $lCExtrasDay = clone collect($lExtras);
                $lCExtrasDay = $lCExtrasDay->where('outDate', $sDate);
                $lCExtrasDay = $lCExtrasDay->where('employee_id', $idEmployee);
                
                $day = new SPrePayrollDay();
                $day->dt_date = $sDate;

                if ($dataType == \SCons::LIMITED_DATA || $dataType == \SCons::ALL_DATA) {
                    /**
                     * Determinar faltas en el periodo
                     */
                    $lColl = clone $lCExtrasDay;
                    $withAbs = $lColl->filter(function ($item) {
                                    return ($item->hasabsence);
                                });
                    $day->num_absences = sizeof($withAbs);
    
                    /**
                     * Determinar primas dominicales
                     */
                    $lColl = clone $lCExtrasDay;
                    $withSunday = $lColl->filter(function ($item) {
                                    return ($item->is_sunday > 0);
                                });
                    $day->is_sunday = sizeof($withSunday);
    
                    /**
                     * Obtención de incidencias o eventos
                     */
                    $lAbsences = prePayrollController::searchAbsence($idEmployee, $sDate);
                    if (sizeof($lAbsences) > 0) {
                        foreach ($lAbsences as $absence) {
                            $key = explode("_", $absence->external_key);
    
                            $abs = [];
                            $abs['id_emp'] = $key[0];
                            $abs['id_abs'] = $key[1];
                            $abs['nts'] = $absence->nts;
    
                            $day->events[] = $abs;
                        }
                    }
                }

                if ($dataType == \SCons::OTHER_DATA || $dataType == \SCons::ALL_DATA) {
                    
                    /**
                     * Obtener descansos
                     */
                    $lColl = clone $lCExtrasDay;
                    // $lColl = $lColl->where('work_dayoff', true);
                    $withDaysOff = $lColl->groupBy('employee_id')->map(function ($row) {
                                                $registry = (object) [
                                                    'daysOff' => $row->sum('work_dayoff'),
                                                ];
    
                                                return $registry;
                                            });
                    if (sizeof($withDaysOff) > 0) {
                        $day->n_days_off = $withDaysOff[$idEmployee]->daysOff;
                    }

                     /**
                     * Obtener festivos
                     */
                    $lColl = clone $lCExtrasDay;
                    $lColl = $lColl->where('is_granted', false)->where('haschecks',1);
                    $withHolidays = $lColl->groupBy('employee_id')->map(function ($row) {
                                                $registry = (object) [
                                                    'holidays' => $row->sum('is_holiday'),
                                                ];
    
                                                return $registry;
                                            });
                    if (sizeof($withHolidays) > 0) {
                        $day->holiday_id = $withHolidays[$idEmployee]->holidays;
                    }
                }

                $row->days[] = $day;
            }

            $prePayroll->rows[] = $row;
        }

        return $prePayroll;
    }

    /**
     * Determina si el empleado tiene incidencias para el día en custión,
     * regresa un arreglo con las incidencias correspondientes
     *
     * @param int $idEmployee
     * @param String $sDate
     * 
     * @return 'Illuminate\Support\Collection' incident
     */
    public static function searchAbsence($idEmployee, $sDate)
    {
        $lAbsences = \DB::table('incidents AS i')
            ->join('type_incidents AS ti', 'i.type_incidents_id', '=', 'ti.id')
            ->where('employee_id', $idEmployee)
            ->whereRaw("'" . $sDate . "' BETWEEN start_date AND end_date")
            ->select('i.external_key', 'i.nts', 'ti.name AS type_name', 'i.id', 'ti.id AS type_id', 'ti.is_allowed')
            ->where('i.is_delete', false)
            ->orderBy('ti.is_agreement', 'ASC')
            ->orderBy('created_by', 'DESC')
            ->orderBy('i.id', 'ASC')
            // Se determinó en algún momento que solo podría haber una incidencia por día
            ->take(1)
            ->get();

        return $lAbsences;
    }

    /**
     * Determina en base a las incidencias por día si el empleado tiene alguna para el día en cuestión,
     * regresa un arreglo con las incidencias correspondientes
     *
     * @param int $idEmployee
     * @param String $sDate
     * 
     * @return incident array
     */
    public static function searchAbsenceByDay($idEmployee, $sDate)
    {
        $lAbsences = \DB::table('incidents AS i')
            ->join('incidents_day AS iday', 'i.id', '=', 'iday.incidents_id')
            ->join('type_incidents AS ti', 'i.type_incidents_id', '=', 'ti.id')
            ->where('employee_id', $idEmployee)
            ->where('iday.date', $sDate)
            ->select('i.external_key', 'i.nts', 'ti.name AS type_name', 'i.id', 'ti.id AS type_id', 'ti.is_allowed')
            ->where('i.is_delete', false)
            ->orderBy('ti.is_agreement', 'ASC')
            ->orderBy('created_by', 'DESC')
            ->orderBy('i.id', 'ASC')
            ->get();

        return $lAbsences;
    }

    /**
     * Importación de fechas de corte de quincenas para prenómina
     */

    public function saveCutCalendarFromJSON($lSiieCutsJ)
    {
        $lCapCuts = cutCalendarQ::select('id', 'external_id')
                                    ->pluck('id', 'external_id');

        $lSiieCutsCol = collect($lSiieCutsJ);
        $lSiieCuts = $lSiieCutsCol->where('fk_tp_pay', 2); // filtrar para quincenas
        
        foreach ($lSiieCuts as $jCut) {
            try {
                if (isset($lCapCuts[$jCut->id_cal])) {
                    $id = $lCapCuts[$jCut->id_cal];
                    $this->updCutPrepayQ($jCut, $id);
                }
                else {
                    $this->insertCutPrepayQ($jCut);
                }
            }
            catch (\Throwable $th) {
                \Log::error($th->getMessage());
            }
        }
    }
    
    private function updCutPrepayQ($jCut, $id)
    {
        cutCalendarQ::where('id', $id)
                    ->update(
                            [
                            'dt_cut' => $jCut->dt_cut,
                            'year' => $jCut->year,
                            'num' => $jCut->num,
                            'is_delete' => $jCut->is_deleted,
                            ]
                        );
    }
    
    private function insertCutPrepayQ($jCut)
    {
        $cut = new cutCalendarQ();

        $cut->year = $jCut->year;
        $cut->num = $jCut->num;
        $cut->dt_cut = $jCut->dt_cut;
        $cut->external_id = $jCut->id_cal;
        $cut->is_delete = $jCut->is_deleted;

        $cut->save();
        $prepayroll = new prepayroll_control();
        $prepayroll->status = 1;
        $prepayroll->num_biweekly = $cut->id;
        $prepayroll->is_biweekly = 1;
        $prepayroll->created_by = session()->get('user_id');
        $prepayroll->updated_by = session()->get('user_id');
        $prepayroll->save();
    }

    public function indexQ(){

        $start_date = null;
        $end_date = null;
        if ($request->start_date == null) {
            $now = Carbon::now();
            $start_date = $now->startOfMonth()->toDateString();
            $end_date = $now->endOfMonth()->toDateString();
        }else {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        }
        
        $quincena = DB::table('prepayroll_control')
                            ->join('hrs_prepay_cut','prepayroll_control.num_biweekly','=','hrs_prepay_cut.id')
                            ->where('prepayroll_control.is_biweekly',1)
                            ->orderBy('hrs_prepay_cut.dt_cut')
                            ->get();  

        return view('prepayroll.indexQ', compact('quincena'));
    }

    public function indexS(Request $request){
        $start_date = null;
        $end_date = null;
        if ($request->start_date == null) {
            $now = Carbon::now();
            $start_date = $now->startOfMonth()->toDateString();
            $end_date = $now->endOfMonth()->toDateString();
        }else {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        }

        $semana = DB::table('prepayroll_control')
                            ->join('week_cut','prepayroll_control.num_week','=','week_cut.id')
                            ->where('prepayroll_control.is_week',1)
                            ->where(function($query) use ($start_date, $end_date){
                                $query->whereBetween('week_cut.ini', [$start_date,$end_date])
                                      ->orWhereBetween('week_cut.fin', [$start_date,$end_date]);
                              })
                            ->orderBy('week_cut.ini')
                            ->select('week_cut.year AS year','week_cut.num AS num','prepayroll_control.status AS status','prepayroll_control.updated_at AS updated_at','prepayroll_control.id AS id','week_cut.ini AS ini','week_cut.fin AS fin')
                            ->get();
        
        return view('prepayrollcontrol.indexS', compact('semana'))->with('start_date',$start_date)->with('end_date',$end_date);
    }

    public function prepayrollBinnacle($id){
        $weekorbi = DB::table('prepayroll_control')->where('prepayroll_control.id',$id)->get();
        
        if($weekorbi[0]->is_week == 1){
            $binnacle = DB::table('prepayrollchanges')
                            ->join('prepayroll_control','prepayroll_control.id','=','prepayrollchanges.prepayroll_id')
                            ->join('users','users.id','=','prepayrollchanges.updated_by')
                            ->join('week_cut','week_cut.id','=','prepayroll_control.num_week')
                            ->where('prepayrollchanges.prepayroll_id',$id)
                            ->orderBy('prepayrollchanges.updated_at','desc')
                            ->select('week_cut.year AS year','week_cut.num AS num','prepayrollchanges.status AS status','prepayrollchanges.updated_at AS updated_at','prepayroll_control.id AS id','week_cut.ini AS ini','week_cut.fin AS fin','users.name AS usuario')
                            ->get();
            $week = 1;
        }else{
            $binnacle = DB::table('prepayrollchanges')
                            ->join('prepayroll_control','prepayroll_control.id','=','prepayrollchanges.prepayroll_id')
                            ->join('users','users.id','=','prepayrollchanges.created_by')
                            ->join('hrs_prepay_cut','hrs_prepay_cut.id','=','prepayroll_control.num_biweekly')
                            ->where('prepayrollchanges.prepayroll_id',$id)
                            ->orderBy('prepayrollchanges.updated_at','desc')
                            ->get();
            $week = 0;
        }
        return view('prepayrollcontrol.binnacle',compact('binnacle'))->with('week',$week);                    
    } 
    
    public function prepayrollS(Request $request){

        $start_date = null;
        $end_date = null;
        if ($request->start_date == null) {
            $now = Carbon::now();
            $start_date = $now->startOfMonth()->toDateString();
            $end_date = $now->endOfMonth()->toDateString();
        }
        else {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        }



        $semana = DB::table('prepayroll_control')
                            ->join('week_cut','prepayroll_control.num_week','=','week_cut.id')
                            ->where('prepayroll_control.is_week',1)
                            ->where(function($query) use ($start_date, $end_date){
                                $query->whereBetween('week_cut.ini', [$start_date,$end_date])
                                      ->orWhereBetween('week_cut.fin', [$start_date,$end_date]);
                              })
                            ->select('week_cut.year AS year','week_cut.num AS num','prepayroll_control.status AS status','prepayroll_control.updated_at AS updated_at','prepayroll_control.id AS id','week_cut.ini AS ini','week_cut.fin AS fin')
                            ->orderBy('week_cut.ini')
                            ->get();
        $aFuera = [];
        for($i = 0 ; count($semana) > $i ; $i++){
            $cambio = DB::table('specialworkshift')->whereBetween('dateI',[$semana[$i]->ini,$semana[$i]->fin])->whereBetween('dateS',[$semana[$i]->ini,$semana[$i]->fin])->where('updated_at','>',$semana[$i]->updated_at)->get();
            $incidencias =  DB::table('incidents')->whereBetween('end_date',[$semana[$i]->ini,$semana[$i]->fin])->whereBetween('start_date',[$semana[$i]->ini,$semana[$i]->fin])->where('updated_at','>',$semana[$i]->updated_at)->get();
            $checadas = DB::table('registers')->whereBetween('date',[$semana[$i]->ini,$semana[$i]->fin])->where('user_id','>',1)->where('updated_at','>',$semana[$i]->updated_at)->get(); 
            
            if((count($cambio) > 0 || count($incidencias) > 0 || count($checadas) > 0) && $semana[$i]->status == 2){
                $aFuera[$i] = 1;   
            }else{
                $aFuera[$i] = 0;
            }
        }

        return view('prepayrollcontrol.controlS',compact('semana'))->with('aFuera',$aFuera)->with('start_date',$start_date)->with('end_date',$end_date); 
    }

    public function prepayrollQ(){
        $start_date = null;
        $end_date = null;
        if ($request->start_date == null) {
            $now = Carbon::now();
            $start_date = $now->startOfMonth()->toDateString();
            $end_date = $now->endOfMonth()->toDateString();
        }else {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        }

        $quincena = DB::table('prepayroll_control')
                            ->join('hrs_prepay_cut','prepayroll_control.num_biweekly','=','hrs_prepay_cut.id')
                            ->where('prepayroll_control.is_biweekly',1)
                            ->where(function($query) use ($start_date, $end_date){
                                $query->whereBetween('week_cut.ini', [$start_date,$end_date])
                                      ->orWhereBetween('week_cut.fin', [$start_date,$end_date]);
                              })
                            ->orderBy('hrs_prepay_cut.dt_cut')
                            ->get();
                 
        for($i = 0 ; count($semana) > $i ; $i++){
            $cambio = DB::table('specialworkshift')->whereBetween('dateI',[$semana->ini,$semana->fin])->whereBetween('dateS',[$semana->ini,$semana->fin])->where('updated_at','>',$end_date)->get();
            $incidencias =  DB::table('incidents')->whereBetween('end_date',[$semana->ini,$semana->fin])->whereBetween('start_date',[$semana->ini,$semana->fin])->where('updated_at','>',$end_date)->get();
            $checadas = DB::table('registers')->whereBetween('date',[$semana->ini,$semana->fin])->where('user_id','>',1)->where('updated_at','>',$end_date)->get(); 
            $aFuera = [];
            if(count($cambio) > 0 || count($incidencias) > 0 || count($checadas) > 0){
                $aFuera[$i] = 1;   
            }else{
                $aFuera[$i] = 0;
            }
        }
                    
        return view('prepayroll.controlS',compact('semana'))->with('aFuera',$aFuera)->with('start_date',$start_date)->with('end_date',$end_date);
    }

    public function bitacorafuera($id){
        $weekorbi = DB::table('prepayroll_control')->where('prepayroll_control.id',$id)->get();
        $week = 0;
        if($weekorbi[0]->is_week == 1){
            $binnacle = DB::table('prepayroll_control')
                            ->join('week_cut','week_cut.id','=','prepayroll_control.num_week')
                            ->where('prepayroll_control.id',$id)
                            ->select('week_cut.year AS year','week_cut.num AS num','prepayroll_control.status AS status','prepayroll_control.updated_at AS updated_at','prepayroll_control.id AS id','week_cut.ini AS ini','week_cut.fin AS fin')
                            ->get();
            $cambio = DB::table('specialworkshift')->whereBetween('dateI',[$binnacle[0]->ini,$binnacle[0]->fin])->whereBetween('dateS',[$binnacle[0]->ini,$binnacle[0]->fin])->where('updated_at','>',$binnacle[0]->updated_at)->get();
            $incidencias =  DB::table('incidents')->whereBetween('end_date',[$binnacle[0]->ini,$binnacle[0]->fin])->whereBetween('start_date',[$binnacle[0]->ini,$binnacle[0]->fin])->where('updated_at',$binnacle[0]->updated_at)->get();
            $checadas = DB::table('registers')->whereBetween('date',[$binnacle[0]->ini,$binnacle[0]->fin])->where('user_id','>',1)->where('updated_at','>',$binnacle[0]->updated_at)->get();
            $week = 1;
        }else{
            $binnacle = DB::table('prepayrollchanges')
                            ->join('prepayroll_control','prepayroll_control.id','=','prepayrollchanges.prepayroll_id')
                            ->join('users','users.id','=','prepayrollchanges.created_by')
                            ->join('hrs_prepay_cut','hrs_prepay_cut.id','=','prepayroll_control.num_biweekly')
                            ->where('prepayrollchanges.prepayroll_id',$id)
                            ->orderBy('prepayrollchanges.updated_at','desc')
                            ->get();
            $week = 0;
        }
        return view('prepayrollcontrol.prepayroll',compact('binnacle'))->with('cambio',$cambio)->with('incidencias',$incidencias)->with('checadas',$checadas)->with('week',$week);    

    }


    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function indexVobos(Request $request, $idPreNomina = 3)
    {
        $startDate = $request->start_date == null ? Carbon::now()->firstOfMonth()->toDateString() : $request->start_date;
        $endDate = $request->end_date == null ? Carbon::now()->lastOfMonth()->toDateString() : $request->end_date;
        $config = \App\SUtils\SConfiguration::getConfigurations();
        
        switch ($idPreNomina) {
            case 'week':
                $lControls = \DB::table('prepayroll_report_auth_controls AS prac')
                            ->join('users AS u', 'prac.user_vobo_id', '=', 'u.id')
                            ->leftJoin('week_cut AS wc', function($join)
                            {
                                $join->on('prac.num_week', '=', 'wc.num');
                                $join->on('wc.year','=', 'prac.year');
                            })
                            ->where(function($query) use ($startDate, $endDate) {
                                $query->where('wc.ini', '!=', null)
                                ->where(function($query2) use ($startDate, $endDate)  {
                                    $query2->whereBetween('wc.ini', [$startDate, $endDate])
                                    ->orWhere(function($query3) use ($startDate, $endDate){
                                        $query3->whereBetween('wc.fin', [$startDate, $endDate]);
                                    });
                                });
                            })
                            ->where('prac.is_delete',0)
							->get();
                break;
            case 'biweek':
                $lControls = \DB::table('prepayroll_report_auth_controls AS prac')
                            ->join('users AS u', 'prac.user_vobo_id', '=', 'u.id')
                            ->leftJoin('hrs_prepay_cut AS hpc', function($join)
                            {
                                $join->on('prac.num_biweek', '=', 'hpc.num');
                                $join->on('hpc.year','=', 'prac.year');
                            })
                            ->where(function($query) use ($startDate, $endDate) {
                                    $query
                                    ->where(function($query4) use ($startDate, $endDate){
                                        $query4->where('hpc.dt_cut', '!=', null)
                                        ->where(function($query5) use ($startDate, $endDate){
                                            $query5->whereBetween('dt_cut', [$startDate, $endDate]);
                                            // ->orWhere(function($query6) use ($startDate, $endDate){
                                            //     $query6->whereBetween(\DB::raw('DATE_SUB(dt_cut,INTERVAL 14 DAY)'), [$startDate, $endDate]);
                                            // });
                                        });
                                    });
                            })
                            ->where('prac.is_delete',0)
							->get();

                foreach($lControls as $cont){
                    $dt_ini = \DB::table('hrs_prepay_cut')
                                        ->where('year', Carbon::parse($startDate)->format('Y'))
                                        ->where('num', ($cont->num_biweek - 1))
                                        ->value('dt_cut');

                    $cont->dt_ini = Carbon::parse($dt_ini)->addDay()->toDateString();
                }
                break;
            default:
                $lControls = \DB::table('prepayroll_report_auth_controls AS prac')
                            ->join('users AS u', 'prac.user_vobo_id', '=', 'u.id')
                            ->leftJoin('week_cut AS wc', function($join)
                            {
                                $join->on('prac.num_week', '=', 'wc.num');
                                $join->on('wc.year','=', 'prac.year');
                            })
                            ->leftJoin('hrs_prepay_cut AS hpc', function($join)
                            {
                                $join->on('prac.num_biweek', '=', 'hpc.num');
                                $join->on('hpc.year','=', 'prac.year');
                            })
                            ->where(function($query) use ($startDate, $endDate) {
                                    $query->where('wc.ini', '!=', null)
                                    ->where(function($query2) use ($startDate, $endDate)  {
                                        $query2->whereBetween('wc.ini', [$startDate, $endDate])
                                        ->orWhere(function($query3) use ($startDate, $endDate){
                                            $query3->whereBetween('wc.fin', [$startDate, $endDate]);
                                        });
                                    })
                                    ->orWhere(function($query4) use ($startDate, $endDate){
                                        $query4->where('hpc.dt_cut', '!=', null)
                                        ->where(function($query5) use ($startDate, $endDate){
                                            $query5->whereBetween('dt_cut', [$startDate, $endDate])
                                            ->orWhere(function($query6) use ($startDate, $endDate){
                                                $query6->whereBetween(\DB::raw('DATE_SUB(dt_cut,INTERVAL 14 DAY)'), [$startDate, $endDate]);
                                            });
                                        });
                                    });
                            })
                            ->where('prac.is_delete',0)
							->get();

                foreach($lControls as $cont){
                    if($cont->is_biweek){
                        $dt_ini = \DB::table('hrs_prepay_cut')
                                            ->where('year', Carbon::parse($startDate)->format('Y'))
                                            ->where('num', ($cont->num_biweek - 1))
                                            ->value('dt_cut');
    
                        $cont->dt_ini = Carbon::parse($dt_ini)->addDay()->toDateString();
                    }
                }
                break;
        }

        $routePrev = route('checkPrevius_vobos');
        $routeChildren = route('checkChildrens_vobos');

        return view('prepayrollcontrol.vobosindex')->with('start_date', $startDate)
                                                    ->with('end_date', $endDate)
                                                    ->with('lControls', $lControls)
                                                    ->with('idPreNomina', $idPreNomina)
                                                    ->with('routePrev', $routePrev)
                                                    ->with('routeChildren', $routeChildren)
                                                    ->with('startOfWeek', $config->startOfWeek);
    }

    public function indexEditRequireVobos(Request $request, $idPreNomina = 3)
    {
        $startDate = $request->start_date == null ? Carbon::now() : $request->start_date;
        $endDate = $request->end_date == null ? Carbon::now()->lastOfMonth()->toDateString() : $request->end_date;
        $config = \App\SUtils\SConfiguration::getConfigurations();
        
        $lControls = \DB::table('prepayroll_report_auth_controls AS prac')
                            ->join('users AS u', 'prac.user_vobo_id', '=', 'u.id')
                            ->leftJoin('week_cut AS wc', function($join)
                            {
                                $join->on('prac.num_week', '=', 'wc.num');
                                $join->on('wc.year','=', 'prac.year');
                            })
                            ->leftJoin('hrs_prepay_cut AS hpc', function($join)
                            {
                                $join->on('prac.num_biweek', '=', 'hpc.num');
                                $join->on('hpc.year','=', 'prac.year');
                            })
                            ->whereRaw("((wc.ini IS NOT NULL AND (wc.ini BETWEEN '".$startDate."' AND '".$endDate."' OR wc.fin BETWEEN '".$startDate."' AND '".$endDate."')) OR 
                                        (hpc.dt_cut IS NOT NULL AND (dt_cut BETWEEN '".$startDate."' AND '".$endDate."' OR DATE_SUB(dt_cut,INTERVAL 14 DAY) BETWEEN '".$startDate."' AND '".$endDate."')))")
                            ->where('prac.is_delete',0)
                            ->orderBy('prac.is_week')
                            ->orderBy('prac.num_week')
                            ->orderBy('prac.num_biweek')
							->get();
        $routeSave = route('vobos_save_require');
        
        foreach($lControls as $oCtrl){
            $oCtrl->ini = $oCtrl->num_week > 0 ? $oCtrl->ini : \Carbon\Carbon::parse($oCtrl->dt_cut)->subDays(14)->toDateString();
            $oCtrl->fin = $oCtrl->num_week > 0 ? $oCtrl->fin : $oCtrl->dt_cut;
        }

        return view('prepayrollcontrol.voboxEditRequireIndex')->with('start_date', $startDate)
                                                    ->with('end_date', $endDate)
                                                    ->with('lControls', $lControls)
                                                    ->with('idPreNomina', $idPreNomina)
                                                    ->with('routeSave', $routeSave)
                                                    ->with('startOfWeek', $config->startOfWeek);
    }

    public function saveRequire(Request $request){
        $result = 0;
        try{
                $result = \DB::table('prepayroll_report_auth_controls')
                            ->where('id_control', $request->idControl)
                            ->where('user_vobo_id', $request->user_id)
                            ->update([
                                'is_required' => $request->require
                            ]);
                
                $value = \DB::table('prepayroll_report_auth_controls')
                            ->where('id_control', $request->idControl)
                            ->where('user_vobo_id', $request->user_id)
                            ->value('is_required');
        }catch (QueryException $e) {
            $result = 0;
        }
        
        return response()->json(array('result'=> $result, 'value' => $value), 200);
    }

    /**
     * Comprueba si la semana/quincena anterior tiene visto bueno
     */
    public function checkBoVoPrevius(Request $request) {
        if ($request->idprenomina == "week") {

            $num = \DB::table('prepayroll_report_auth_controls')
                        ->where('id_control', $request->id)
                        ->select('num_week', 'year')
                        ->first();

            $anterior = \DB::table('prepayroll_report_auth_controls')
                                ->where('year', $num->year)
                                ->where('is_delete', 0)
                                ->where('num_week', $num->num_week - 1)
                                ->where('user_vobo_id', auth()->user()->id)
                                ->value('is_vobo');

        }
        else if ($request->idprenomina == "biweek") {

            $num = \DB::table('prepayroll_report_auth_controls')
                        ->where('id_control', $request->id)
                        ->select('num_biweek', 'year')
                        ->first();

            $anterior = \DB::table('prepayroll_report_auth_controls')
                            ->where('is_delete', 0)
                            ->where('year', $num->year)
                            ->where('num_biweek', $num->num_biweek - 1)
                            ->where('user_vobo_id', auth()->user()->id)
                            ->value('is_vobo');
        }

        return response()->json(array('previus' => $anterior), 200);
    }

    /**
     * Comprueba si los grupos hijos ya han dado el visto bueno a una semana/quincena
     */
    public function checkBoVoChildrens(Request $request) {
        $usersNotVobo = [];
        $sectNum = "";

        $oPpAuthCtrol = \DB::table('prepayroll_report_auth_controls')
                    ->where('id_control', $request->id)
                    ->select('num_'.$request->idprenomina, 'year', 'is_global', 'order_vobo')
                    ->first();
        
        if ($request->idprenomina == "week") {
            $sectNum = $oPpAuthCtrol->num_week;
        }
        else if ($request->idprenomina == "biweek") {
            $sectNum = $oPpAuthCtrol->num_biweek;
        }

        $groups = \DB::table('prepayroll_groups AS pg')
                        ->join('prepayroll_groups_users AS pgu', 'pg.id_group', '=', 'pgu.group_id')
                        ->where('pgu.head_user_id', auth()->user()->id)
                        ->pluck('pg.id_group')
                        ->toArray();

        if (count($groups) > 0) {
            $lChildrenGroups = SPrepayrollUtils::getChildrenOfGroups($groups);
    
            $lGroupsHeads = \DB::table('prepayroll_groups AS pg')
                                    ->join('prepayroll_groups_users AS pgu', 'pg.id_group', '=', 'pgu.group_id')
                                    ->join('users AS u', 'pgu.head_user_id', '=', 'u.id')
                                    ->whereIn('pg.id_group', $lChildrenGroups)
                                    ->where('pgu.head_user_id', '<>', auth()->user()->id)
                                    ->select('pg.id_group','pgu.head_user_id', 'group_name', 'u.name AS user_name')
                                    ->get();
            
            foreach($lGroupsHeads as $group) {
                $is_vobo = \DB::table('prepayroll_report_auth_controls')
                                ->where('year', $oPpAuthCtrol->year)
                                ->where('num_'.$request->idprenomina, $sectNum)
                                ->where('user_vobo_id', $group->head_user_id)
                                ->where('user_vobo_id', '<>', auth()->user()->id)
                                ->where('is_required', true);
    
                $aux = clone $is_vobo;
                $aux = $aux->get();
    
                // si el usuario no está en los vobos
                if ($aux->count() == 0) {
                    continue;
                }
    
                $is_vobo = $is_vobo->value('is_vobo');
                if (! $is_vobo) {
                    $nameUser = $group->user_name;
                    array_push($usersNotVobo, $nameUser);
                }
            }
        }
        else if ($oPpAuthCtrol->is_global) {
            $AllVobos = \DB::table('prepayroll_report_auth_controls AS prac')
                                ->join('users AS u', 'prac.user_vobo_id', '=', 'u.id')
                                ->where('year', $oPpAuthCtrol->year)
                                ->where('num_'.$request->idprenomina, $sectNum)
                                ->where('order_vobo', '<', $oPpAuthCtrol->order_vobo)
                                ->where('is_required', true)
                                ->where('is_vobo', false)
                                ->select('is_vobo', 'u.name AS user_name')
                                ->get();

            if (count($AllVobos) > 0) {
                foreach ($AllVobos as $oVobo) {
                    array_push($usersNotVobo, $oVobo->user_name);   
                }
            }
        }

        $bCanSkip = false;
        if (count($usersNotVobo) > 0) {
            $vobosMajor = \DB::table('prepayroll_report_auth_controls AS prac')
                                ->join('users AS u', 'prac.user_vobo_id', '=', 'u.id')
                                ->where('year', $oPpAuthCtrol->year)
                                ->where('num_'.$request->idprenomina, $sectNum)
                                ->where('order_vobo', '>=', $oPpAuthCtrol->order_vobo)
                                ->where('user_vobo_id', '<>', auth()->user()->id)
                                ->where('is_required', true)
                                // ->where('is_vobo', false)
                                ->select('is_vobo', 'u.name AS user_name')
                                ->get();

            $bCanSkip = count($vobosMajor) == 0;
        }

        return response()->json(array(
                                        'users' => $usersNotVobo,
                                        'bCanSkip' => $bCanSkip
                                    ), 200);
    }

    /**
     * Dar visto bueno a una prenómina
     *
     * @param int $id
     * 
     * @return redirect
     */
    public function boVo(Request $request, $idVobo, $idPreNomina = 1) {
        $success = true;

        try {
            if (! isset($request->can_skip) || $request->can_skip == 0) {
                /**
                 * Determinar si ya se ha dado Vobo a los empleados directos del usuario en sesión
                */

                $isAllOk = SPrepayrollUtils::isAllEmployeesOk(auth()->user()->id, $idVobo);

                if (! $isAllOk) {
                    $success = false;
                    throw new \Exception('No se puede dar Vobo, no se han dado visto bueno a todos los empleados directos del usuario en sesión.');
                }

                //determinar si la fecha ya paso y se puede dar visto bueno
                $greaterThan = SPrepayrollUtils::isAdvancedDate($idVobo);   

                if ($greaterThan == false) {
                    $success = false;
                    throw new \Exception('No se puede dar Vobo, fecha de corte aun no a pasado.');
                }

                
            }
            else {
                $oAuthCtrl = PrepayReportControl::find($idVobo);

                if ($oAuthCtrl != null) {
                    $oSkipped = new prepayrollVoboSkipped();

                    $oSkipped->is_week = $oAuthCtrl->is_week;
                    $oSkipped->num_week = $oAuthCtrl->num_week;
                    $oSkipped->is_biweek = $oAuthCtrl->is_biweek;
                    $oSkipped->num_biweek = $oAuthCtrl->num_biweek;
                    $oSkipped->year = $oAuthCtrl->year;
                    $oSkipped->dt_skipped = Carbon::now()->toDateTimeString();
                    $oSkipped->is_delete = 0;
                    $oSkipped->skipped_by_id = auth()->user()->id;

                    $oSkipped->save();
                }
            }

            $res = \DB::table('prepayroll_report_auth_controls')
                        ->where('id_control', $idVobo)
                        ->where('user_vobo_id', auth()->user()->id)
                        ->update([
                            'is_vobo' => true, 
                            'dt_vobo' => Carbon::now()->toDateTimeString(),
                            'is_rejected' => false,
                            'dt_rejected' => null
                        ]);
        }
        catch (\Exception $e) {
            return redirect()->route('vobos', $idPreNomina)->with(['error' => $e->getMessage(), 'icon' => 'error']);
        }

        $msg = "Se dió el visto bueno correctamente";
        $icon = "success";
        
        // $res = \DB::table('prepayroll_report_auth_controls')
        //             ->where('id_control', $id)
        //             ->update([
        //                 'is_vobo' => true, 
        //                 'dt_vobo' => Carbon::now()->toDateTimeString(),
        //                 'is_rejected' => false,
        //                 'dt_rejected' => null
        //             ]);
        
        // $config = \App\SUtils\SConfiguration::getConfigurations();
        // if($config->prepayroll_policy == 2){
        //     $nivelAprobado = DB::table('prepayroll_report_auth_controls')
        //                         ->where('id_control',$id)
        //                         ->select('is_week AS isWeek','is_biweek AS isBiweek','num_week AS numWeek','num_biweek AS numBiweek','user_vobo_id AS usuario','year AS anio')
        //                         ->get();
            
        //     if($nivelAprobado[0]->isWeek == 1){
        //         $tipo = 1;
        //         $numfecha = DB::table('week_cut')
        //                             ->where('num',$nivelAprobado[0]->numWeek)
        //                             ->where('year',$nivelAprobado[0]->anio)
        //                             ->select('id AS semana')
        //                             ->get();
        //         $fecha = $numfecha[0]->semana;

        //         $nivelMaximo = DB::table('prepayroll_report_configs')
        //                     ->where('is_required', 1)
        //                     ->where('is_week',1)
        //                     ->orderBy('order_vobo','desc')
        //                     ->take(1)
        //                     ->select('user_n_id  AS usuario')
        //                     ->get();

        //     }else{
        //         $tipo = 2;

        //         $numfecha = DB::table('hrs_prepay_cut')
        //                             ->where('num',$nivelAprobado[0]->numBiweek)
        //                             ->where('year',$nivelAprobado[0]->anio)
        //                             ->select('id AS quincena')
        //                             ->get();
        //         $fecha = $numfecha[0]->quincena;
                
        //         $nivelMaximo = DB::table('prepayroll_report_configs')
        //                     ->select('user_n_id  AS usurio')       
        //                     ->where('is_required', 1)
        //                     ->where('is_biweek',1)
        //                     ->orderBy('order_vobo','desc')
        //                     ->take(1)
        //                     ->select('user_vobo_id  AS usuario')
        //                     ->get();

        //     }
            
            
        //     if( $nivelMaximo[0]->usuario == $nivelAprobado[0]->usuario ){
        //         if( $tipo == 1){
        //             $prepayrollAUX = prepayroll_control::where('num_week',$fecha)->get();

        //             $prepayroll = prepayroll_control::find($prepayrollAUX[0]->id);
        //             $prepayroll->status = 2;
        //             $prepayroll->updated_by = session()->get('user_id');
        //             $prepayroll->save();
        
        //             $change = new prepayrollchange();
        //             $change->prepayroll_id = $prepayroll->id;
        //             $change->status = 2;
        //             $change->created_by = session()->get('user_id');
        //             $change->updated_by = session()->get('user_id');
        //             $change->save();   
        //         }else{
        //             $prepayrollAUX  = prepayroll_control::where('num_biweekly',$fecha)->get();

        //             $prepayroll = prepayroll_control::find($prepayrollAUX[0]->id);
        //             $prepayroll->status = 2;
        //             $prepayroll->updated_by = session()->get('user_id');
        //             $prepayroll->save();

        //             $change = new prepayrollchange();
        //             $change->prepayroll_id = $prepayroll->id;
        //             $change->status = 2;
        //             $change->created_by = session()->get('user_id');
        //             $change->updated_by = session()->get('user_id');
        //             $change->save();
        //         }
        //     }else{
        //         if( $tipo == 1){
        //             $prepayrollAUX = prepayroll_control::where('num_week',$fecha)->get();

        //             $prepayroll = prepayroll_control::find($prepayrollAUX[0]->id);
        //             $prepayroll->status = 1;
        //             $prepayroll->updated_by = session()->get('user_id');
        //             $prepayroll->save();
        
        //             $change = new prepayrollchange();
        //             $change->prepayroll_id = $prepayroll->id;
        //             $change->status = 1;
        //             $change->created_by = session()->get('user_id');
        //             $change->updated_by = session()->get('user_id');
        //             $change->save();   
        //         }else{
        //             $prepayrollAUX = prepayroll_control::where('num_biweekly',$fecha)->get();

        //             $prepayroll = prepayroll_control::find($prepayrollAUX[0]->id);
        //             $prepayroll->status = 1;
        //             $prepayroll->updated_by = session()->get('user_id');
        //             $prepayroll->save();

        //             $change = new prepayrollchange();
        //             $change->prepayroll_id = $prepayroll->id;
        //             $change->status = 1;
        //             $change->created_by = session()->get('user_id');
        //             $change->updated_by = session()->get('user_id');
        //             $change->save();
        //         }   
        //     }
        // }

        return redirect()->route('vobos',['idPreNomina' => $idPreNomina])->with(['mensaje' => $msg, 'icon' => $icon]);
    }

    /**
     * Rechazar visto bueno de prenómina
     *
     * @param [type] $id
     * @return void
     */
    public function rejBoVo($id, $idPreNomina = 1)
    {
        $res = \DB::table('prepayroll_report_auth_controls')
                    ->where('id_control', $id)
                    ->update([
                        'is_vobo' => false, 
                        'dt_vobo' => null,
                        'is_rejected' => true,
                        'dt_rejected' => Carbon::now()->toDateTimeString()
                    ]);

        return redirect()->route('vobos', ['idPreNomina' => $idPreNomina]);
    }
    public function prepayrollAbrir($id){
        $control = prepayroll_control::find($id);

        $control->status = 0;
        $control->updated_by = session()->get('user_id');
        $control->save();
    
        $change = new prepayrollchange();
        $change->prepayroll_id = $control->id;
        $change->status = 0;
        $change->created_by = session()->get('user_id');
        $change->updated_by = session()->get('user_id');
        $change->save();

        if($control->is_week == 1){
            // si el que llega es una semana
            $fecha = DB::table('week_cut')
                            ->where('id',$control->num_week)
                            ->get();
            $res = \DB::table('prepayroll_report_auth_controls')
                            ->where('num_week', $fecha[0]->num)
                            ->where('year', $fecha[0]->year)
                            ->update([
                                'is_vobo' => false, 
                                'dt_vobo' => null,
                                'is_rejected' => false,
                                'dt_rejected' => null
                            ]);
            return redirect()->route('control_semana');
        }else{
            // si el que llega es una quincena
            $fecha = DB::table('hrs_prepay_cut')
                            ->where('id',$control->num_biweekly)
                            ->get();
            
            $res = \DB::table('prepayroll_report_auth_controls')
                            ->where('num_biweek', $fecha[0]->num)
                            ->where('year', $fecha[0]->year)
                            ->update([
                                'is_vobo' => false, 
                                'dt_vobo' => null,
                                'is_rejected' => false,
                                'dt_rejected' => null
                            ]);
            return redirect()->route('control_quincena');
        }

    }
}
