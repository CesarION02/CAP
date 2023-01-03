<?php namespace App\SUtils\Logger;

    use App\Models\PrepayrollLog;
    use Illuminate\Support\Facades\Hash;

    /**
     * Clase que se encarga del guardado de registros de log cuando el sistema cambia la orientación de
     * las checadas, las omite o cambia el horario del horario en base al horario detectado
     */
    class SLogger {

        protected $startDate;
        protected $endDate;
        protected $wayPayId;
        protected $idGeneration;

        public function __construct(string $startDate, string $endDate, int $wayPayId) {
            $this->startDate = $startDate;
            $this->endDate = $endDate;
            $this->wayPayId = $wayPayId;
            $this->idGeneration = Hash::make($startDate.$endDate.$wayPayId.(isset(\Auth::user()->id) ? \Auth::user()->id : 1));
        }

        /**
         * Genera un registro log que se guarda en la base de datos depende del tipo de ajuste del sistema
         *
         * @param int $employeeId id del empleado
         * @param string $adjustBySystem puede ser: enum('checada_omitida','checada_cambio','cambio_horario')
         * @param int $idCheck
         * @param int $checkOrigType
         * @param string $programmedSch
         * @param string $detectedSch
         * 
         * @return void
         */
        public function log($employeeId, $adjustBySystem, $idCheck, $checkOrigType, $programmedSch, $detectedSch) {
            try {
                $obj = new PrepayrollLog();
    
                $obj->id_generation = $this->idGeneration;
                $obj->start_date = $this->startDate;
                $obj->end_date = $this->endDate;
                $obj->programmed_schedule_n = $programmedSch;
                $obj->detected_schedule_n = $detectedSch;
                $obj->adjust_by_system = $adjustBySystem;
                $obj->register_n_id = $idCheck;
                $obj->type_reg_orig_n_id = $checkOrigType;
                $obj->way_pay_id = $this->wayPayId > 0 ? $this->wayPayId : null;
                $obj->employee_id = $employeeId;
                $obj->user_by_id = (isset(\Auth::user()->id) ? \Auth::user()->id : 1);
    
                $obj->save();
            }
            catch (\Throwable $th) {
                \Log::error($th);
            }
        }
    }

?>