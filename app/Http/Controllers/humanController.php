<?php

namespace App\Http\Controllers;

use App\SUtils\SHumanUtils;
use Illuminate\Http\Request;

class humanController extends Controller
{
    public function testHuman()
    {
        SHumanUtils::sendShiftsToHuman(
            '2025-01-20',
            '2025-01-26',
            [1550,1598]
        );
    }
}