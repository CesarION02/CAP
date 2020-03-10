<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\company;

class companyController extends Controller
{

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        $iFilter = $request->filter_acts == null ? 1 : $request->filter_acts;

        $data = null;

        switch ($iFilter) {
            case 1:
                $data = company::where('is_delete', false)->get();
                break;
            case 2:
                $data = company::where('is_delete', true)->get();
                break;
            
            default:
                $data = company::get();
                break;
        }

        return view('companies.index')->with('lCompanies', $data)
                                        ->with('iFilter', $iFilter);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $comp = json_decode($request->company);

        $oCompany = new company();
        $oCompany->name = $comp->name;
        $oCompany->fiscal_id = $comp->fiscal_id;
        $oCompany->created_by = 1;
        $oCompany->updated_by = 1;

        $oCompany->save();

        return json_encode($oCompany);
    }

    /**
     * Undocumented function
     *
     * @param Type $var
     * @return void
     */
    public function update(Request $request, $id)
    {
        $comp = json_decode($request->company);
        $oCompany = company::find($id);

        $oCompany->name = $comp->name;
        $oCompany->fiscal_id = $comp->fiscal_id;
        $oCompany->updated_by = 1;

        $oCompany->save();

        return json_encode($oCompany);
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @return void
     */
    public function destroy($id)
    {
        $oCompany = company::find($id);

        if ($oCompany->is_delete) {
            company::where('id', $id)
                            ->update(['is_delete' => false]);
        }
        else {
            company::where('id', $id)
                            ->update(['is_delete' => true]);
        }

        return $id;
    }
}
