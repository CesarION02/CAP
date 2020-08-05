<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\prepayrollAdjust;
use App\SUtils\SPrepayrollAdjustUtils;

class prepayrollAdjustController extends Controller
{
    
    public function getAdjustsFromRow(Request $request)
    {
        $lAdjusts = \DB::table('prepayroll_adjusts AS pa')
                        ->join('prepayroll_adjusts_types AS pat', 'pa.adjust_type_id', '=', 'pat.id')
                        ->select('pa.employee_id',
                                    'pa.dt_date',
                                    'pa.dt_time',
                                    'pa.minutes',
                                    'pa.adjust_type_id',
                                    'pa.apply_to',
                                    'pa.comments',
                                    'pat.type_code',
                                    'pat.type_name',
                                    'pa.id'
                                    )
                        ->whereBetween('dt_date', [$request->start_date, $request->end_date])
                        ->where('is_delete', false)
                        ->where('pa.employee_id', $request->employee_id)
                        ->get();

        return json_encode($lAdjusts);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function storeAdjust(Request $request)
    {
        $oAdjust = new prepayrollAdjust($request->all());
        
        $oAdjust->is_delete = false;
        $oAdjust->created_by = \Auth::user()->id;
        $oAdjust->updated_by = \Auth::user()->id;

        $oAdjust->save();

        SPrepayrollAdjustUtils::verifyProcessedData($oAdjust->employee_id, $oAdjust->dt_date);

        return json_encode($oAdjust);
    }

    public function deleteAdjust($idAjust)
    {
        $oAdjust = prepayrollAdjust::find($idAjust);

        $oAdjust->is_delete = true;
        $oAdjust->updated_by = \Auth::user()->id;

        $oAdjust->save();

        SPrepayrollAdjustUtils::verifyProcessedData($oAdjust->employee_id, $oAdjust->dt_date);

        return json_encode($oAdjust);
    }

}
