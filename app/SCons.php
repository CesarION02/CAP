<?php
namespace App;

class SCons {
   const REP_DELAY = 1;
   const REP_HR_EX = 2;
   const REP_HR_EX_TOT = 3;

   const REG_IN = 1;
   const REG_OUT = 2;

   const WEEK_START_DAY = 1; //LUNES

   const PAY_W_Q = 1;
   const PAY_W_S = 2;

   const ALL_DATA = "1";
   const LIMITED_DATA = "2";
   const OTHER_DATA = "3";

   const BEN_POL_STRICT = 1;
   const BEN_POL_FREE = 2;
   const BEN_POL_EVENT = 3;

   const ET_POL_NEVER = 1;	// Nunca genera
   const ET_POL_ALWAYS = 2; // Siempre genera
   const ET_POL_SOMETIMES = 3; //En ocasiones genera

   const T_DAY_NORMAL = 1;
   const T_DAY_INHABILITY = 2;
   const T_DAY_VACATION = 3;
   const T_DAY_HOLIDAY = 4;
   const T_DAY_DAY_OFF = 5;

   const CL_ABSENCE = 1;
   const CL_INHABILITY = 2;
   const CL_VACATIONS = 3;

   const PP_TYPES = 
                  [
                     'JE' => 1,
                     'JS' => 2,
                     'OR' => 3,
                     'OF' => 4,
                     'DHE' => 5,
                     'AHE' => 6,
                     'COM' => 7,
                     'JSA' => 8
                  ];

   const FROM_ASSIGN = 2;
   const FROM_WORKSH = 3;

   const INACTIVE_DAY = 15;
   
   const OVERTIME_CHECK_POLICY_IN = 1;
   const OVERTIME_CHECK_POLICY_OUT = 2;
   const INC_TYPE = [
      'INA_S_PER' => 1, // INASIST. S/PERMISO
      'INA_C_PER_SG' => 2,// INASIST. C/PERMISO S/GOCE
      'INA_C_PER_CG' => 3,// INASIST. C/PERMISO C/GOCE
      'INA_AD_REL_CH' => 4,// INASIST. ADMTIVA. RELOJ CHECADOR
      'INA_AD_SUSP' => 5,// INASIST. ADMTIVA. SUSPENSIÓN
      'INA_AD_OT' => 6,// INASIST. ADMTIVA. OTROS
      'ONOM_EXT' => 7,// ONOMÁSTICO
      'RIESGO' => 8,// Riesgo de trabajo
      'ENFERMEDAD' => 9,// Enfermedad en general
      'MATER' => 10,// Maternidad
      'LIC_CUIDADOS' => 11,// Licencia por cuidados médicos de hijos diagnosticados con cáncer.
      'VAC' => 12,// VACACIONES
      'VAC_PEND' => 13,// VACACIONES PENDIENTES
      'CAPACIT' => 14,// CAPACITACIÓN
      'TRAB_F_PL' => 15,// TRABAJO FUERA PLANTA
      'PATER' => 16,// PATERNIDAD
      'DIA_OTOR' => 17,// DIA OTORGADO
      'INA_PRES_MED' => 18,// INASIST. PRESCRIPCION MEDICA
      'DESCANSO' => 19,// DESCANSO
      'INA_TR_F_PL' => 20,// INASIST. TRABAJO FUERA DE PLANTA
      'VAC_CAP' => 21,// VACACIONES
      'INC_CAP' => 22,// INCAPACIDAD
      'ONOM_CAP' => 23,// ONOMÁSTICO
      'PERM' => 24,// PERMISO
      'DAY_HOLIDAY' => 25,// PERMISO
      'PERM_BY_GONE' => 26// PERMISO
   ];

   const TASK_TYPE_REPORT_JOURNEY = 1;
   const TASK_TYPE_REPORT_OTHER = 2;
   const TASK_TYPE_ADJUST_PGH = 3;
}

?>