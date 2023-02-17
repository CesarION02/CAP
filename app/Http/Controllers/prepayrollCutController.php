<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\week_cut;
use Carbon\Carbon;
use DB;

class prepayrollCutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $year = null;

        if ($request->year == null) {
            $now = Carbon::now();
            $year = $now->year;
        }else {
            $year = $request->year;
        }

        $datas = week_cut::orderBy('ini')
                        ->where('year', $year)
                        ->get();

        
        return view('prepayrollcut.index')
                        ->with('year', $year)
                        ->with('datas', $datas);
    }

    public function indexBiweek(Request $request){
        $year = null;

        if ($request->year == null) {
            $now = Carbon::now();
            $year = $now->year;
        }else {
            $year = $request->year;
        }
        $datas = DB::table('hrs_prepay_cut')
                        ->where('year',$year)
                        ->where('is_delete', 0)                                
                        ->get();
        
        return view('prepayrollcut.indexbiweek')
                        ->with('year', $year)
                        ->with('datas', $datas);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
