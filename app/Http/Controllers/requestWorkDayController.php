<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SUtils\SPrepayrollUtils;
use DB;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class requestWorkDayController extends Controller
{
    public function index($id = null){
        $config = \App\SUtils\SConfiguration::getConfigurations();
        $isAdmin = false;
        foreach(auth()->user()->roles()->get() as $rol){
            $result = in_array($rol->id, $config->rolesCanSeeAll);
            if($result){
                $isAdmin = true;
                break;
            }
        }

        if($isAdmin){
            $lUsers = DB::table('users')
                        ->join('prepayroll_groups_users as pru','pru.head_user_id','=','users.id')
                        ->select('users.id','users.name')
                        ->orderBy('users.name')
                        ->get();
        }else{
            $lUsers = DB::table('users')
                    ->join('prepayroll_groups_users as pru','pru.head_user_id','=','users.id')
                    ->where('users.id',auth()->user()->id)
                    ->select('users.id','users.name')
                    ->orderBy('users.name')
                    ->get();

            $id = auth()->user()->id;
        }

        $payType = 0;
        $bDirect = true;
        $subEmployees = SPrepayrollUtils::getEmployeesByUser($id, $payType, $bDirect, null);

        $lEmployees = \DB::table('employees AS e')
                            ->leftJoin('prepayroll_group_employees AS pge', 'e.id', '=', 'pge.employee_id')
                            ->leftJoin('prepayroll_groups AS pg', 'pge.group_id', '=', 'pg.id_group')
                            ->leftJoin('prepayroll_groups_users AS pgu', 'pge.group_id', '=', 'pgu.group_id')
                            ->leftJoin('users AS u', 'pgu.head_user_id', '=', 'u.id')
                            ->leftJoin('departments as dep', 'dep.id', '=', 'e.department_id')
                            ->leftJoin('prepayroll_group_deptos AS pgd', 'dep.id', '=', 'pgd.department_id')
                            ->leftJoin('prepayroll_groups AS pgDep', 'pgd.group_id', '=', 'pgDep.id_group')
                            ->select('e.id as employee_id', 'e.num_employee', 'e.name as employee', 'pg.id_group',
                            'pg.group_name as group_name_employee', 'pg.father_group_n_id','u.name AS gr_titular', 'dep.name as department',
                            'pgDep.group_name as group_name_depto')
                            ->whereIn('e.id',$subEmployees)
                            ->where('e.external_id','!=',null)
                            ->get();

        $date = Carbon::now();
        $lSundays = [];
        for ($i=(int)$date->format('W'); $i < 52; $i++) { 
            array_push($lSundays, ['('.$date->format('W').')', $date->endOfWeek()->format('Y-m-d'), 'Domingo']);
            $date->addWeeks(1);
        }
        
        $workshifts = DB::table('workshifts')->where('is_delete',0)->get();
        
        return view('requestWorkDay.index',['isAdmin' => $isAdmin, 'lUsers' => $lUsers, 'lEmployees' => $lEmployees, 'idUser' => $id, 'lSundays' => $lSundays, 'workshifts' => $workshifts]);
    }

    public function generate(Request $request)
    {
        $idUser = $request->user;

        return redirect(route('request_work_day', ['id' => $idUser]));
    }

    public function getPDF(Request $request){
        $employees = DB::table('employees as emp')
                        ->leftJoin('dept_rh as dep', 'dep.id', '=', 'emp.dept_rh_id')
                        ->select('emp.id as employee_id', 'emp.name as employee', 'dep.id as department_id', 'dep.name as department')
                        ->whereIn('emp.id', $request->generateCheck)
                        ->get();
        
        $user = DB::table('users')->where('id',$request->user_id)->first();

        $date = Carbon::createFromFormat('Y-m-d',$request->fecha);

        $workshift = DB::table('workshifts')->where('id',$request->turno)->select('entry','departure')->first();
        $this->makePDF($employees, $user->name, $date, $workshift);
    }

    public function makePDF($employees, $user, $date, $workshift){
        $header = 
        '
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left; width: 20%;"><img src="./images/logo_HD.png" style="width: 3cm;"></th>
                        <th style="width: 80%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2" style="width: 100%; text-align: center; font-size: 0.6cm;"><b>SOLICITUD PARA PRESENTARSE A LABORAR</b></td>
                    </tr>
                </tbody>
            </table>
        ';

        $footer = 
        '
            <table style="width: 100%;">
                <tbody>
                    <tr>
                        <td style="text-align: center;"></td>
                        <td style="text-align: center; font-size: 0.4cm;"><i>'.$user.'</i></td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">__________________________</td>
                        <td style="text-align: center;">__________________________</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; font-size: 0.3cm; padding: 0.3cm;">Nombre y firma del empleado</td>
                        <td style="text-align: center; font-size: 0.3cm; padding: 0.3cm;">Nombre y firma del solicitante</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; padding: 0.3cm;">Aceptación</td>
                        <td style="text-align: center; padding: 0.3cm;">Jefe de Área</td>
                    </tr>
                </tbody>
            </table>
        ';

        $html = [];

        $months = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $daysWeek = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado'];

        foreach($employees as $emp){
            array_push( $html,
            '
            <div class = "container">
                <div>
                    <p style="font-size: 0.4cm;">Solicito a usted presentarse a laborar el dia mencionado, por temas de carga de trabajo. De acuardo al art. 75 de la LFT.</p>
                </div>
                <br>
                <div>
                    <p style="font-size: 0.4cm;">Nombre del empleado: <b><u>'.$emp->employee.'</u></b><p>
                </div>
                <br>
                <div>
                    <p style="font-size: 0.4cm;">Área donde se requiere: <u>'.$emp->department.'</u><p>
                </div>
                <br>
                <div>
                    <p style="font-size: 0.4cm;">Fecha y horario en que se presentará: '
                    .$daysWeek[$date->dayOfWeek].' '.$date->format('d').' de '.$months[$date->month].' '.$date->format('Y').' '.$workshift->entry.' a '.$workshift->departure.'<p>
                </div>
                <br>
                <br>
                <div>
                    <p style="font-size: 0.4cm;">Se firma de aceptación del empleado al margen del mismo, al igual que la firma del jefe del área que solicita la presencia del empleado.<p>
                </div>
            </div>
            '
            );
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'c',
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 55,
            'margin_bottom' => 30,
            'margin_header' => 10,
            'margin_footer' => 10
        ]);

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;
        $mpdf->use_kwt = true;

        $stylesheet = file_get_contents('./mpdf/mpdfMycss.css');
        
        $mpdf->SetTitle('SOLICITUD PARA PRESENTARSE A LABORAR '.$date->format('Y-m-d'));
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLfooter($footer);
        $mpdf->WriteHTML($stylesheet, 1);
        for ($i=0; $i < sizeof($employees); $i++) { 
            $mpdf->WriteHTML($html[$i],2);
            if(($i + 1) < sizeof($employees)){
                $mpdf->AddPage();
            }
        }
        $mpdf->Output('SOLICITUD PARA PRESENTARSE A LABORAR_'.$date->format('Y-m-d').'.pdf', 'D');
    }
}
