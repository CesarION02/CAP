<?php namespace App\SUtils;

use GuzzleHttp\Client;

class SHumanUtils
{
    public static function sendShiftsToHuman($startDate, $endDate, $employeeIds = []) {

        if (empty($employeeIds)) {

            $employeeIds = \DB::table('employees')
                ->where('is_delete', false)
                ->pluck('id')
                ->toArray();
        }

        $workshifts = self::getWorkshifts(
            $startDate,
            $endDate,
            $employeeIds
        );

        $employees = [];

        $grouped = collect($workshifts)->groupBy('id');

        foreach ($grouped as $employeeId => $days) {

            $employeeDays = [];

            foreach ($days as $day) {

                $employeeDays[] = [
                    'date' => $day->date,

                    'timeSlots' => [
                        [
                            substr($day->entry, 0, 5),
                            substr($day->departure, 0, 5)
                        ]
                    ],

                    'isWorkday' => true
                ];
            }

            $employees[] = [
                'employeeId' => (string) $employeeId,
                // 'employeeId' => '1020',
                'days' => $employeeDays
            ];
        }

        $payload = [
            'employees' => $employees
        ];

        $client = new Client();

        $response = $client->post(
            env('HUMAN_API_URL') . '/shifts/bulk-create',
            [
                'headers' => [
                    'Authorization' => 'Basic ' . env('HUMAN_TOKEN'),
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],

                'json' => $payload
            ]
        );

        $body = json_decode(
            $response->getBody()->getContents(),
            true
        );

        dd($body);
    }

    public static function getWorkshifts($startDate, $endDate, $lEmployees)
    {
        $lWorkshifts = \DB::table('week_department_day AS wdd')
                            ->join('day_workshifts AS dw', 'wdd.id', '=', 'dw.day_id')
                            ->join('day_workshifts_employee AS dwe', 'dw.id', '=', 'dwe.day_id')
                            ->join('workshifts AS w', 'dw.workshift_id', '=', 'w.id')
                            ->join('type_day AS td', 'dwe.type_day_id', '=', 'td.id')
                            ->join('employees AS e', 'dwe.employee_id', '=', 'e.id')
                            ->select('wdd.date AS date',  
                                        'w.entry AS entry', 
                                        'w.departure', 
                                        'w.cut_id',
                                        'e.id')
                            ->where('dwe.is_delete', false)
                            ->where('w.is_delete', false)
                            ->where('e.is_delete', false)
                            ->whereBetween('wdd.date', [$startDate, $endDate]);

        if (sizeof($lEmployees) > 0) {
            $lWorkshifts = $lWorkshifts->whereIn('dwe.employee_id', $lEmployees);
        }

        return $lWorkshifts->get();
    }
}