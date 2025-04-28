<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepartmentRH;
use App\Models\JobRH;
use App\Models\department;
use App\Models\positionGroupDept;
use App\Models\positionPrepayroll;
use App\SUtils\SAssignGroups;
use DB;

class JobRhController extends Controller
{
    private $lDepartments;

    public function index(Request $request)
    {
        $iFilter = $request->ifilter == 0 ? 1 : $request->ifilter;

        switch ($iFilter) {
            case 1:
                $datas = JobRH::where('is_deleted','0')->orderBy('job')->get();
                $datas->each(function($datas){
                    $datas->departmentRH;
                    $datas->department;
                });
                break;
            case 2:
                $datas = JobRH::where('is_deleted','1')->orderBy('job')->get();
                $datas->each(function($datas){
                    $datas->departmentRH;
                    $datas->department;
                });
                break;
            
            default:
                $datas = JobRH::orderBy('job')->get();
                $datas->each(function($datas){
                    $datas->departmentRH;
                    $datas->department;
                });
                break;
        }
        return view('jobRh.index', compact('datas'))->with('iFilter',$iFilter);
    }

    public function saveJobsFromJSON($lSiieJobs)
    {
        $lUnivJobs = JobRH::pluck('id', 'external_id');
        $this->lDepartments = DepartmentRH::pluck('id', 'external_id');
        foreach ($lSiieJobs as $jSiieJob) {
            // dd($lUnivJobs, $this->lDepartments, $jSiieJob);
            try {
                if (isset($lUnivJobs[$jSiieJob->id_position])) {
                    $idJobUniv = $lUnivJobs[$jSiieJob->id_position];
                    $this->updJob($jSiieJob, $idJobUniv);
                }
                else {
                    // dd($jSiieJob);
                    $this->insertJob($jSiieJob);
                }
            }
            catch (\Throwable $th) {
            }
        }
    }
    
    private function updJob($jSiieJob, $idJobUniv)
    {
        JobRH::where('id', $idJobUniv)
                    ->update(
                            [
                                'job' => $jSiieJob->name,
                                'acronym' => $jSiieJob->code,
                                'is_deleted' => $jSiieJob->is_deleted,
                                'dept_rh_id' => $this->lDepartments[$jSiieJob->fk_department]
                            ]
                        );
    }
    
    private function insertJob($jSiieJob)
    {
        $oJob = new JobRH();

        $oJob->job = $jSiieJob->name;
        $oJob->acronym = $jSiieJob->code;
        $oJob->num_positions = 0;
        $oJob->hierarchical_level = 0;
        $oJob->is_deleted = $jSiieJob->is_deleted;
        $oJob->external_id = $jSiieJob->id_position;
        $oJob->dept_rh_id = $this->lDepartments[$jSiieJob->fk_department];

        $oJob->save();
    }

    public function edit($id)
    {
        $data = JobRH::findOrFail($id);
        $departments = department::where('is_delete',0)->orderBy('name', 'ASC')->pluck('id','name');
        return view('jobRH.edit', compact('data'))->with('departments',$departments);
    }

    public function update(Request $request, $id)
    {
        $job = JobRH::findOrFail($id);
        $job->department_id = $request->department_id;
        $job->save();
        return redirect('jobRH')->with('mensaje', 'Puesto RH actualizado con éxito');
    }

    public function showPositions(){
        $jobs = DB::table('job_rh as j')
            ->select(
                'j.id as job_id',
                'j.job',
                DB::raw('GROUP_CONCAT(DISTINCT dg.name SEPARATOR ", ") as dept_groups'),
                DB::raw('GROUP_CONCAT(DISTINCT pg.group_name SEPARATOR ", ") as prepayroll_groups')
            )
            ->leftJoin('position_grp_dept as pd', 'pd.job_rh_id', '=', 'j.id')
            ->leftJoin('department_group as dg', 'dg.id', '=', 'pd.grp_dept_id')
            ->leftJoin('position_prepayroll as pp', 'pp.job_rh_id', '=', 'j.id')
            ->leftJoin('prepayroll_groups as pg', 'pg.id_group', '=', 'pp.grp_prepayroll_id')
            ->where('j.is_deleted', 0)
            ->groupBy('j.id', 'j.job')
            ->get();
        
        return view('jobRh.showPosition', compact('jobs'));
        //dd($jobs);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editPositions($id)
    {
        $group_departments = DB::table('department_group')
            ->select('id', 'name')
            ->where('is_delete', 0)
            ->get(); 
            
        $group_prepayroll = DB::table('prepayroll_groups')
            ->select('id_group', 'group_name')
            ->where('is_delete', 0)
            ->get();
  
        $datasDG = DB::table('job_rh as j')
            ->select(
                'pd.grp_dept_id AS pd_id',
            )
            ->leftJoin('position_grp_dept as pd', 'pd.job_rh_id', '=', 'j.id')
            ->where('j.is_deleted', 0)
            ->where('j.id', $id)            
            ->get();
        $seleccionadosDG = isset($datasDG) ? $datasDG->pluck('pd_id')->toArray() : [];
        
        $datasPG = DB::table('job_rh as j')
            ->select(
                'pp.grp_prepayroll_id AS pp_id'
            )
            ->leftJoin('position_prepayroll as pp', 'pp.job_rh_id', '=', 'j.id')
            ->where('j.is_deleted', 0)
            ->where('j.id', $id)            
            ->get();

        $seleccionadosPG = isset($datasPG) ? $datasPG->pluck('pp_id')->toArray() : [];

        $datas = DB::table('job_rh as j')
            ->where('j.is_deleted', 0)
            ->where('j.id', $id)
            ->get();

        //dd($datasDG);   
        return view('jobRh.editPosition')
                ->with('group_departments',$group_departments)
                ->with('group_prepayroll',$group_prepayroll)
                ->with('seleccionadosDG',$seleccionadosDG)
                ->with('seleccionadosPG',$seleccionadosPG)
                ->with('datas',$datas);     
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePositions(Request $request, $id)
    {
        //dd($request);
        if (empty($request->dg) && empty($request->pg)) {
            return redirect()->back()->with('error', 'No seleccionaste ningún grupo. No se realizó ninguna modificación.');
        }

        DB::beginTransaction();
        try {
          
            $borrarDept = positionGroupDept::where('job_rh_id',$id)->delete();
            $borrarPre = positionPrepayroll::where('job_rh_id', $id)->delete();

            if (!empty($request->dg)) {
                foreach($request->dg as $dg){
                    $pgd = new positionGroupDept();
                    $pgd->job_rh_id = $id;
                    $pgd->grp_dept_id = $dg;
                    $pgd->created_by = auth()->user()->id;
                    $pgd->updated_by = auth()->user()->id;
                    $pgd->save();
                }
            }
            
            if (!empty($request->pg)) {
                foreach($request->pg as $pg){
                    $ppd = new positionPrepayroll();
                    $ppd->job_rh_id = $id;
                    $ppd->grp_prepayroll_id = $pg;
                    $ppd->created_by = auth()->user()->id;
                    $ppd->updated_by = auth()->user()->id;
                    $ppd->save();
                }
            }

            //inicia la función que revisará a quienes se le cambiara
            $users = DB::table('users')
                        ->join('employees','users.employee_id', '=', 'employees.id')
                        ->select('users.id AS id_user')
                        ->where('employees.job_rh_id', $id)
                        ->get();
            
            if ($users->isNotEmpty()) {
                // Convertir en array simple de IDs, por ejemplo:
                $userIds = $users->pluck('id_user')->toArray();
                SAssignGroups::assignGroup($userIds, $request->dg, $request->pg);
                
                // Ahora $userIds es algo como: [3, 7, 15]
            } else {
                $userIds = []; // vacío si no hay resultados
            }
          DB::commit();
          return redirect('showPositions')->with('mensaje','Se actualizo correctamente');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Ocurrió un error: ' . $th->getMessage());
        }
        
    }
}
