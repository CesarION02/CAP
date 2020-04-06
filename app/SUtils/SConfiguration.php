<?php namespace App\SUtils;

use Carbon\Carbon;

class SConfiguration {

    public static function getConfigurations()
    {
        // Read File
        $jsonString = file_get_contents(base_path('cap_config.json'));
        $data = json_decode($jsonString);

        return $data;
    }

    public static function setConfiguration($key, $value)
    {
        // Read File
        $jsonString = file_get_contents(base_path('cap_config.json'));
        $data = json_decode($jsonString, true);

        // Update Key
        $data[$key] = $value;

        // Write File
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents(base_path('cap_config.json'), stripslashes($newJsonString));
    }
}

?>