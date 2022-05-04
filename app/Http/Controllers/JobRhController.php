<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepartmentRH;
use App\Models\JobRH;

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
                });
                break;
            case 2:
                $datas = JobRH::where('is_deleted','1')->orderBy('job')->get();
                $datas->each(function($datas){
                    $datas->departmentRH;
                });
                break;
            
            default:
                $datas = JobRH::orderBy('job')->get();
                $datas->each(function($datas){
                    $datas->departmentRH;
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
}
