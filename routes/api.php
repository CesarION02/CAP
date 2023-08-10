<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'api\\AuthController@login');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/*
* Prenómina
**/
Route::get('prepayroll', [
    'uses' => 'prePayrollController@getPrePayroll'
]);
/*
* Faltas y retardos
**/
Route::get('absdelays', [
    'uses' => 'externalSrcsController@getAbsDelays'
]);

Route::group(['middleware' => 'auth:api'], function() {

    /**
     * API Control Access CAP
    */

    /*
    * get employees
    **/
    Route::get('employees', [
        'uses' => 'AccessControlController@getEmployees'
    ]);
    /*
    * get id employee by ID
    **/
    Route::get('infobynum', [
        'uses' => 'AccessControlController@getInfoByEmployeeNumber'
    ]);
    /*
    * get id employee by ID
    **/
    Route::get('infobyid', [
        'uses' => 'AccessControlController@getAllInfoById'
    ]);
});

Route::group(['middleware' => 'auth:api'], function() {

    /**
     * API External Incidents
    */

    /*
    * save incidents
    **/
    Route::post('saveincident', [
        'uses' => 'api\\ExternalIncidentsController@saveIncident'
    ]);

    /*
    * save adjust
    **/
    Route::post('saveadjust', [
        'uses' => 'api\\ExternalAdjustsController@saveAdjust'
    ]);

    /*
    * cancel incidents
    **/
    Route::post('cancelincident', [
        'uses' => 'api\\ExternalIncidentsController@cancelIncidents'
    ]);

    /*
    * cancel adjust
    **/
    Route::post('canceladjust', [
        'uses' => 'api\\ExternalAdjustsController@cancelAdjust'
    ]);

});

