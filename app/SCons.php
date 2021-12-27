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
                     'AHE' => 6
                  ];

   const FROM_ASSIGN = 2;
   const FROM_WORKSH = 3;

   const INACTIVE_DAY = 15;
   
   const OVERTIME_CHECK_POLICY_IN = 1;
   const OVERTIME_CHECK_POLICY_OUT = 2;
}

?>