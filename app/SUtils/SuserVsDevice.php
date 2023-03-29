<?php namespace App\SUtils;

/**
 * Estructura Utilizada para mapear usuarios vs devices
 * 
 */
class SuserVsDevice {
    public $idUser;
    public $nameEmployee;
    public $devices;

    function __construct()
    {
        $idUser = '';
        $nameEmployee = '';
        $devices = '';
    }
}

?>