<?php namespace App\SUtils;

use App\Mail\checadorVsNominaMail;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Models\incident;
use Carbon\Carbon;
class SChecadorVsNominaUtils {
    public static function sendExcel($lData, $start_date, $end_date, $start_date_siie, $end_date_siie, $mails){
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $documento = new Spreadsheet();
        $documento
            ->getProperties()
            ->setCreator("CAP")
            ->setLastModifiedBy('CAP') // última vez modificado por
            ->setTitle('Reporte checador vs nomina')
            ->setSubject('Reporte checador vs nomina')
            ->setDescription('Este documento fue generado por CAP')
            ->setKeywords('')
            ->setCategory('');

        $hoja = $documento->getActiveSheet();
        $hoja->setTitle("checador vs nomina");

        $lTitles = collect($config->columnTitles);

        foreach($lTitles as $title){
            if($title->id != 2 && $title->id != 5){
                $hoja->setCellValueByColumnAndRow($title->column, $title->row, $title->value);
            }else if ($title->id == 2){
                $hoja->setCellValueByColumnAndRow($title->column, $title->row, SDateFormatUtils::formatDate($start_date, 'D/mm/Y').' al '.SDateFormatUtils::formatDate($end_date, 'D/mm/Y'));
            }else if ($title->id == 5){
                $hoja->setCellValueByColumnAndRow($title->column, $title->row, SDateFormatUtils::formatDate($start_date, 'D/mm/Y').' al '.SDateFormatUtils::formatDate($end_date, 'D/mm/Y'));
            }
        }

        $lColumnsDimensions = collect($config->excelColumnDimensions);

        foreach($lColumnsDimensions as $col){
            $hoja->getColumnDimension($col->columna)->setWidth($col->dimension);
        }

        $hoja->freezePane('A1');
        $hoja->freezePane('A2');
        $hoja->freezePane('A4');

        $style = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '8EE78D',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => [
                        'argb' => 'FF000000',
                    ],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $hoja->getStyle('A3:AA3')->applyFromArray($style);

        $style2 = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => [
                        'argb' => 'FF000000',
                    ],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];
        $style3 = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'DADADA',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => [
                        'argb' => 'FF000000',
                    ],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $countBlack = 0;
        $countWhite = 0;
        $black = $config->cebraReport->black;
        $white = $config->cebraReport->white;
        for($i = 0; $i < count($lData); $i++){
            $hoja->setCellValueByColumnAndRow($lTitles->where('id', 7)->first()->column, ($i + 4), $lData[$i]->num_employee);
            $hoja->setCellValueByColumnAndRow($lTitles->where('id', 8)->first()->column, ($i + 4), $lData[$i]->name);
            $hoja->setCellValueByColumnAndRow($lTitles->where('id', 9)->first()->column, ($i + 4), $lData[$i]->workedDays);

            foreach($lData[$i]->incidences as $inc){
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 10)->first()->column, ($i + 4), $inc['otherIncidents']);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 11)->first()->column, ($i + 4), $inc['faltas']);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 13)->first()->column, ($i + 4), $inc['vacations']);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 15)->first()->column, ($i + 4), $inc['onomasticos']);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 17)->first()->column, ($i + 4), $inc['perConGoce']);
            }

            foreach($lData[$i]->ears as $ear){
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 12)->first()->column, ($i + 4), $ear->not_work);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 35)->first()->column, ($i + 4), $ear->have_bonus);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 22)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->time_delay_real));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 23)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->time_delay_justified));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 24)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->time_delay_permission));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 25)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->early_departure_original));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 26)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->early_departure_permission));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 27)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_stps));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 36)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_schedule));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 28)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_work));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 29)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_adjust));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 30)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_total));

                foreach($ear->rows as $row){
                    foreach($row->column as $col){
                        if($row->external_id == 3){
                            $hoja->setCellValueByColumnAndRow($col, ($i + 4), SChecadorVsNominaUtils::numToHours($row->tot_unt));
                        }else{
                            $hoja->setCellValueByColumnAndRow($col, ($i + 4), $row->tot_unt);
                        }
                    }
                }
            }

            if($config->cebraReport->vertical){
                $columnas = $hoja->getColumnIterator();
                $alternarColor = true;
                $contadorColumnas = 0;
                $numeroColumnasMaximo = 27;
                $countBlack = 0;
                $countWhite = 0;
                $change = false;
                foreach ($columnas as $columna) {
                    $letraColumna = $columna->getColumnIndex();

                    if($countBlack < $black){
                        $alternarColor = $change == true ? !$alternarColor : $alternarColor;
                        $countBlack++;
                        $countWhite = 0;
                        $change = $countBlack >= $black;
                    }else if($countWhite < $white){
                        $alternarColor = $change == true ? !$alternarColor : $alternarColor;
                        $countWhite++;
                        $countBlack = $countWhite < $white ? $countBlack : 0;
                        $change = $countWhite >= $white;
                    }

                    if ($contadorColumnas < $numeroColumnasMaximo) {
                        if ($alternarColor) {
                            $hoja->getStyle($letraColumna.($i + 4).':'.$letraColumna.($i + 4))->applyFromArray($style3);
                        } else {
                            $hoja->getStyle($letraColumna.($i + 4).':'.$letraColumna.($i + 4))->applyFromArray($style2);
                        }
                    }

                    $contadorColumnas++;
                }
            }else{
                if($countBlack < $black){
                    $hoja->getStyle('A'.($i + 4).':AA'.($i + 4))->applyFromArray($style3);
                    $countBlack++;
                    $countWhite = 0;
                }else if($countWhite < $white){
                    $hoja->getStyle('A'.($i + 4).':AA'.($i + 4))->applyFromArray($style2);
                    $countWhite++;
                    $countBlack = $countWhite < $white ? $countBlack : 0;
                }
            }

            $hoja->getRowDimension(($i + 4))->setRowHeight($config->excelRowDimension);
        }

        $nombreDelDocumento = "checadorVsNomina.xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreDelDocumento . '"');
        header('Cache-Control: max-age=0');
        
        $writer = IOFactory::createWriter($documento, 'Xlsx');

        $tempFilePath = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFilePath);

        $ccs = explode(";", $mails->cco);
        Mail::to($mails->to)->cc($ccs)->send(new checadorVsNominaMail($tempFilePath, $start_date, $end_date));
        unlink($tempFilePath);
    }

    public static function downloadExcel($lData, $start_date, $end_date, $start_date_siie, $end_date_siie){
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $documento = new Spreadsheet();
        $documento
            ->getProperties()
            ->setCreator("CAP")
            ->setLastModifiedBy('CAP') // última vez modificado por
            ->setTitle('Reporte checador vs nomina')
            ->setSubject('Reporte checador vs nomina')
            ->setDescription('Este documento fue generado por CAP')
            ->setKeywords('')
            ->setCategory('');

        $hoja = $documento->getActiveSheet();
        $hoja->setTitle("checador vs nomina");

        $lTitles = collect($config->columnTitles);

        foreach($lTitles as $title){
            if($title->id != 2 && $title->id != 5){
                $hoja->setCellValueByColumnAndRow($title->column, $title->row, $title->value);
            }else if ($title->id == 2){
                $hoja->setCellValueByColumnAndRow($title->column, $title->row, $start_date_siie.' al '.$end_date_siie);
            }else if ($title->id == 5){
                $hoja->setCellValueByColumnAndRow($title->column, $title->row, $start_date.' al '.$end_date);
            }
        }

        $lColumnsDimensions = collect($config->excelColumnDimensions);

        foreach($lColumnsDimensions as $col){
            $hoja->getColumnDimension($col->columna)->setWidth($col->dimension);
        }

        $hoja->freezePane('A1');
        $hoja->freezePane('A2');
        $hoja->freezePane('A4');

        $style = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '8EE78D',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => [
                        'argb' => 'FF000000',
                    ],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $hoja->getStyle('A3:AA3')->applyFromArray($style);

        $style2 = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => [
                        'argb' => 'FF000000',
                    ],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];
        $style3 = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'DADADA',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => [
                        'argb' => 'FF000000',
                    ],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        $countBlack = 0;
        $countWhite = 0;
        $black = $config->cebraReport->black;
        $white = $config->cebraReport->white;
        for($i = 0; $i < count($lData); $i++){
            $hoja->setCellValueByColumnAndRow($lTitles->where('id', 7)->first()->column, ($i + 4), $lData[$i]->num_employee);
            $hoja->setCellValueByColumnAndRow($lTitles->where('id', 8)->first()->column, ($i + 4), $lData[$i]->name);
            $hoja->setCellValueByColumnAndRow($lTitles->where('id', 9)->first()->column, ($i + 4), $lData[$i]->workedDays);

            foreach($lData[$i]->incidences as $inc){
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 10)->first()->column, ($i + 4), $inc['otherIncidents']);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 11)->first()->column, ($i + 4), $inc['faltas']);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 13)->first()->column, ($i + 4), $inc['vacations']);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 15)->first()->column, ($i + 4), $inc['onomasticos']);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 17)->first()->column, ($i + 4), $inc['perConGoce']);
            }

            foreach($lData[$i]->ears as $ear){
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 12)->first()->column, ($i + 4), $ear->not_work);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 35)->first()->column, ($i + 4), $ear->have_bonus);
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 22)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->time_delay_real));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 23)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->time_delay_justified));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 24)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->time_delay_permission));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 25)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->early_departure_original));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 26)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->early_departure_permission));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 27)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_stps));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 36)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_schedule));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 28)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_work));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 29)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_adjust));
                $hoja->setCellValueByColumnAndRow($lTitles->where('id', 30)->first()->column, ($i + 4), SChecadorVsNominaUtils::minutesToHours($ear->te_total));

                foreach($ear->rows as $row){
                    foreach($row->column as $col){
                        if($row->external_id == 3){
                            $hoja->setCellValueByColumnAndRow($col, ($i + 4), SChecadorVsNominaUtils::numToHours($row->tot_unt));
                        }else{
                            $hoja->setCellValueByColumnAndRow($col, ($i + 4), $row->tot_unt);
                        }
                    }
                }
            }

            if($config->cebraReport->vertical){
                $columnas = $hoja->getColumnIterator();
                $alternarColor = true;
                $contadorColumnas = 0;
                $numeroColumnasMaximo = 27;
                $countBlack = 0;
                $countWhite = 0;
                $change = false;
                foreach ($columnas as $columna) {
                    $letraColumna = $columna->getColumnIndex();

                    if($countBlack < $black){
                        $alternarColor = $change == true ? !$alternarColor : $alternarColor;
                        $countBlack++;
                        $countWhite = 0;
                        $change = $countBlack >= $black;
                    }else if($countWhite < $white){
                        $alternarColor = $change == true ? !$alternarColor : $alternarColor;
                        $countWhite++;
                        $countBlack = $countWhite < $white ? $countBlack : 0;
                        $change = $countWhite >= $white;
                    }

                    if ($contadorColumnas < $numeroColumnasMaximo) {
                        if ($alternarColor) {
                            $hoja->getStyle($letraColumna.($i + 4).':'.$letraColumna.($i + 4))->applyFromArray($style3);
                        } else {
                            $hoja->getStyle($letraColumna.($i + 4).':'.$letraColumna.($i + 4))->applyFromArray($style2);
                        }
                    }

                    $contadorColumnas++;
                }
            }else{
                if($countBlack < $black){
                    $hoja->getStyle('A'.($i + 4).':AA'.($i + 4))->applyFromArray($style3);
                    $countBlack++;
                    $countWhite = 0;
                }else if($countWhite < $white){
                    $hoja->getStyle('A'.($i + 4).':AA'.($i + 4))->applyFromArray($style2);
                    $countWhite++;
                    $countBlack = $countWhite < $white ? $countBlack : 0;
                }
            }

            $hoja->getRowDimension(($i + 4))->setRowHeight($config->excelRowDimension);
        }

        $nombreDelDocumento = "checadorVsNomina.xlsx";
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreDelDocumento . '"');
        header('Cache-Control: max-age=0');
        
        $writer = IOFactory::createWriter($documento, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public static function getIncidences($employee_id, $start_date, $end_date, $type_prepayroll = null, $prepayroll = null){
        $lWorked = \DB::table('processed_data as p')
                            ->where('employee_id', $employee_id)
                            ->where('inDate', '>=', $start_date)
                            ->where('outDate', '<=', $end_date)
                            ->where([['hasabsence', 0], ['haschecks', 1]]);
    
        if(!is_null($type_prepayroll) && !is_null($prepayroll)){
            if($type_prepayroll == 1){
                $lWorked = $lWorked->where('biweek', $prepayroll);
            }else{
                $lWorked = $lWorked->where('week', $prepayroll);
            }
        }

        $lWorked = $lWorked->get();

        $lIncidents = \DB::table('incidents as i')
                        ->join('incidents_day as d', 'd.incidents_id', '=', 'i.id')
                        ->join('type_incidents as t', 't.id', '=', 'i.type_incidents_id')
                        ->where('i.employee_id', $employee_id)
                        ->where('i.is_delete', 0)
                        ->where('d.is_delete', 0)
                        ->whereBetween('d.date', [$start_date, $end_date])
                        ->whereNotIn('d.date', $lWorked->pluck('inDate')->toArray())
                        ->whereNotIn('d.date', $lWorked->pluck('outDate')->toArray())
                        ->whereNotIn('i.type_incidents_id', [19])
                        ->select(
                            'i.start_date',
                            'i.end_date',
                            'i.eff_day',
                            'i.nts',
                            'i.employee_id',
                            'i.type_incidents_id',
                            'i.is_external',
                            't.name',
                            'd.incidents_id',
                            'd.id as day_id',
                            'd.date',
                            'd.num_day',
                        )
                        ->get();

        foreach($lIncidents as $inc){
            $sameDay = $lIncidents->where('date', $inc->date);
            if(count($sameDay) < 2){
                
            }else{
                $sameDayInternal = $sameDay->where('is_external', 0);
                $sameDayExternal = $sameDay->where('is_external', 1);
                if(count($sameDayExternal) > 0){
                    $registro = $sameDayExternal->where('date', $sameDayExternal->max('date'))->last();
                }else{
                    $registro = $sameDayInternal->where('date', $sameDayInternal->max('date'))->last();
                }

                $lDeleteDay = $sameDay->where('day_id', '!=', $registro->day_id)->keys();
                foreach($lDeleteDay as $d){
                    $lIncidents->forget($d);
                }
            }
        }

        $lVacations = $lIncidents->where('type_incidents_id', 12);
        $lOnomastico = $lIncidents->where('type_incidents_id', 7);
        $lPerConGoce = $lIncidents->where('type_incidents_id', 3);
        
        $lPerSinGoce = $lIncidents->where('type_incidents_id', 2);
        $lSinPer = $lIncidents->where('type_incidents_id', 1);

        $lOtherIncidents = $lIncidents->whereNotIn('type_incidents_id', [12,7,3,2,1,19]);

        $lAuxInc = $lIncidents->whereNotIn('type_incidents_id', [1]);
        $lFaltas = \DB::table('processed_data as p')
                        ->where('employee_id', $employee_id)
                        ->where('inDate', '>=', $start_date)
                        ->where('outDate', '<=', $end_date)
                        ->whereNotIn('inDate', $lAuxInc->pluck('inDate')->toArray())
                        ->whereNotIn('outDate', $lAuxInc->pluck('outDate')->toArray());

        if(!is_null($type_prepayroll) && !is_null($prepayroll)){
            if($type_prepayroll == 1){
                $lFaltas = $lFaltas->where('biweek', $prepayroll);
            }else{
                $lFaltas = $lFaltas->where('week', $prepayroll);
            }
        }

        $lFaltas = $lFaltas->where('hasabsence', 1)
                           ->get();

        $faltas = count($lFaltas) + count($lPerSinGoce) + count($lSinPer);
        $vacations = count($lVacations);
        $onomasticos = count($lOnomastico);
        $perConGoce = count($lPerConGoce);
        $otherIncidents = count($lOtherIncidents);

        return collect(
                [
                    [
                        "faltas" => $faltas,
                        "vacations" => $vacations,
                        "onomasticos" => $onomasticos,
                        "perConGoce" => $perConGoce,
                        "otherIncidents" => $otherIncidents
                    ]
                ]);
    }

    public static function getEars($employee_id, $start_date, $end_date, $type_prepayroll, $prepayroll){
        $config = \App\SUtils\SConfiguration::getConfigurations();

        $empPayroll = \DB::table('emp_vs_payroll')
                        ->where('emp_id', $employee_id);

        if($type_prepayroll == 1){
            $empPayroll = $empPayroll->where('num_biweek', $prepayroll);
        }else{
            $empPayroll = $empPayroll->where('num_week', $prepayroll);
        }

        $empPayroll = $empPayroll->first();

        if(is_null($empPayroll)){
            return [];
        }

        $earns_payroll = \DB::table('earns_payroll as ep')
                            ->join('earnings as e', 'e.external_id', '=', 'ep.ear_id')
                            ->where('empvspayroll_id', $empPayroll->id_empvspayroll)
                            ->select(
                                'ep.*',
                                'e.name_ear',
                                'e.external_id',
                                \DB::raw('SUM(unt) as tot_unt')
                            )
                            ->groupBy(['ear_id'])
                            ->get();

        $earVsColumns = collect($config->earVsColumns);
        foreach($earns_payroll as $ep){
            $ep->column = $earVsColumns->where('id', $ep->external_id)->first()->column;
        }

        $arrIds = $earns_payroll->pluck('external_id');

        $lNotEmpPayroll = \DB::table('earnings')
                            ->whereNotIn('external_id', $arrIds)
                            ->get();

        foreach($lNotEmpPayroll as $nep){
            $nep->tot_unt = 0;
            $nep->column = $earVsColumns->where('id', $nep->external_id)->first()->column;
        }

        $earns = $earns_payroll->merge($lNotEmpPayroll);

        $empPayroll->rows = $earns;

        return collect([$empPayroll]);
    }

    public static function getWorkedDays($employee_id, $start_date, $end_date, $type_prepayroll, $prepayroll){
       $lWorked = \DB::table('processed_data as p')
                            ->where('employee_id', $employee_id)
                            ->where('inDate', '>=', $start_date)
                            ->where('outDate', '<=', $end_date)
                            ->where([['hasabsence', 0], ['haschecks', 1]]);
    
        if(!is_null($type_prepayroll) && !is_null($prepayroll)){
            if($type_prepayroll == 1){
                $lWorked = $lWorked->where('biweek', $prepayroll);
            }else{
                $lWorked = $lWorked->where('week', $prepayroll);
            }
        }

        $lWorked = $lWorked->get();

        $lIncidents = \DB::table('incidents as i')
                            ->join('incidents_day as d', 'd.incidents_id', '=', 'i.id')
                            ->join('type_incidents as t', 't.id', '=', 'i.type_incidents_id')
                            ->where('i.employee_id', $employee_id)
                            ->where('i.is_delete', 0)
                            ->where('d.is_delete', 0)
                            ->whereBetween('d.date', [$start_date, $end_date])
                            ->whereNotIn('d.date', $lWorked->pluck('inDate')->toArray())
                            ->whereNotIn('d.date', $lWorked->pluck('outDate')->toArray())
                            ->whereNotIn('i.type_incidents_id', [19])
                            ->select(
                                'i.start_date',
                                'i.end_date',
                                'i.eff_day',
                                'i.nts',
                                'i.employee_id',
                                'i.type_incidents_id',
                                'i.is_external',
                                't.name',
                                'd.incidents_id',
                                'd.id as day_id',
                                'd.date',
                                'd.num_day',
                            )
                            ->get();

        foreach($lIncidents as $inc){
            $sameDay = $lIncidents->where('date', $inc->date);
            if(count($sameDay) < 2){

            }else{
                $sameDayInternal = $sameDay->where('is_external', 0);
                $sameDayExternal = $sameDay->where('is_external', 1);
                if(count($sameDayExternal) > 0){
                    $registro = $sameDayExternal->where('date', $sameDayExternal->max('date'))->last();
                }else{
                    $registro = $sameDayInternal->where('date', $sameDayInternal->max('date'))->last();
                }

                $lDeleteDay = $sameDay->where('day_id', '!=', $registro->day_id)->keys();
                foreach($lDeleteDay as $d){
                    $lIncidents->forget($d);
                }
            }
        }

        $lDayOff = \DB::table('processed_data as p')
                            ->where('employee_id', $employee_id)
                            ->where('inDate', '>=', $start_date)
                            ->where('outDate', '<=', $end_date)
                            ->whereNotIn('inDate', $lWorked->pluck('inDate')->toArray())
                            ->whereNotIn('outDate', $lWorked->pluck('outDate')->toArray())
                            ->whereNotIn('inDate', $lIncidents->pluck('date')->toArray())
                            ->whereNotIn('outDate', $lIncidents->pluck('date')->toArray())
                            ->where('is_dayoff', 1);
    
        if(!is_null($type_prepayroll) && !is_null($prepayroll)){
            if($type_prepayroll == 1){
                $lDayOff = $lDayOff->where('biweek', $prepayroll);
            }else{
                $lDayOff = $lDayOff->where('week', $prepayroll);
            }
        }

        $lDayOff = $lDayOff->get();

        return (count($lWorked) + count($lDayOff));
    }

    public static function minutesToHours($minutes){
        $signo = "";
        if($minutes < 0){
            $minutes = $minutes * -1;
            $signo = "-";
        }

        $horas = floor($minutes / 60);
        $restoMinutos = $minutes % 60;

        return $signo.str_pad($horas, 2, "0", STR_PAD_LEFT).":".str_pad($restoMinutos, 2, "0", STR_PAD_LEFT);
    }

    public static function numToHours($num){
        $horas = floor($num);
        $minutos = fmod($num, 1) * 60;

        return str_pad($horas, 2, "0", STR_PAD_LEFT).":".str_pad($minutos, 2, "0", STR_PAD_LEFT);
    }

    public static function getReport($cfg, $prepayroll){
        try {
            $oCfg = json_decode($cfg);
            $type_prepayroll = $oCfg->pay_type;

            if($type_prepayroll == \SCons::PAY_W_Q){
                $prepayroll = str_replace('Q_', '', $prepayroll);
    
                $end_date = \DB::table('hrs_prepay_cut as h')
                                ->where('id', $prepayroll)
                                ->value('dt_cut');
    
                $date = \DB::table('hrs_prepay_cut as h')
                                ->where('id', ((Int)$prepayroll)-1)
                                ->value('dt_cut');
    
                $start_date = Carbon::parse($date)->addDay()->toDateString();
            }else if ($type_prepayroll == \SCons::PAY_W_S){
                $prepayroll = str_replace('S_', '', $prepayroll);
                
                $start_date = \DB::table('week_cut as h')
                                ->where('id', $prepayroll)
                                ->value('ini');

                $end_date = \DB::table('week_cut as h')
                                ->where('id', $prepayroll)
                                ->value('fin');
            }

            $lEmployeesAreas = \DB::table('employees as e')
                        ->join('departments as d', 'd.id', '=', 'e.department_id')
                        ->whereIn('d.area_id', $oCfg->areas)
                        ->where('e.is_active', 1)
                        ->where('e.is_delete', 0)
                        ->where('e.way_pay_id', $type_prepayroll)
                        ->select(
                            'e.id as employee_id',
                            'e.num_employee',
                            'e.name'
                        )
                        ->get();

            $lEmployeesDepartments = \DB::table('employees as e')
                        ->whereIn('e.department_id', $oCfg->departments)
                        ->where('e.is_active', 1)
                        ->where('e.is_delete', 0)
                        ->where('e.way_pay_id', $type_prepayroll)
                        ->select(
                            'e.id as employee_id',
                            'e.num_employee',
                            'e.name'
                        )
                        ->get();

            $lEmployeesEmps = \DB::table('employees as e')
                        ->whereIn('id', $oCfg->employees)
                        ->where('e.is_active', 1)
                        ->where('e.is_delete', 0)
                        ->where('e.way_pay_id', $type_prepayroll)
                        ->select(
                            'e.id as employee_id',
                            'e.num_employee',
                            'e.name'
                        )
                        ->get();

            $lEmployees = $lEmployeesAreas->merge($lEmployeesDepartments)->merge($lEmployeesEmps);
            $lEmployees = $lEmployees->unique('employee_id')->sortBy('name');

            foreach($lEmployees as $emp){
                $emp->ears = SChecadorVsNominaUtils::getEars($emp->employee_id, $start_date, $end_date, $type_prepayroll, $prepayroll);
                $emp->incidences = SChecadorVsNominaUtils::getIncidences($emp->employee_id, $start_date, $end_date, $type_prepayroll, $prepayroll);
                $emp->workedDays = SChecadorVsNominaUtils::getWorkedDays($emp->employee_id, $start_date, $end_date, $type_prepayroll, $prepayroll);
                if(count($emp->ears) == 0){
                    $indexEmp = $lEmployees->where('employee_id', $emp->employee_id)->keys();
                    foreach($indexEmp as $index){
                        $lEmployees->forget($index);
                    }
                }
            }
            $lEmployees = $lEmployees->values();
            // SChecadorVsNominaUtils::downloadExcel($lEmployees, $start_date, $end_date, $lEmployees[0]->ears[0]->external_date_ini, $lEmployees[0]->ears[0]->external_date_end);
            SChecadorVsNominaUtils::sendExcel($lEmployees, $start_date, $end_date, $lEmployees[0]->ears[0]->external_date_ini, $lEmployees[0]->ears[0]->external_date_end, $oCfg->mails);
        } catch (\Throwable $th) {
            \Log::error($th);
            return $th->getMessage();
        }

        return "";
    }
}
?>