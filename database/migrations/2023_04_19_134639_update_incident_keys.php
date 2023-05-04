<?php

use App\Models\incident;
use App\Models\IncidentExtSysLink;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateIncidentKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $lIncidents = incident::where('external_key', '!=', '0_0')
                                ->whereNotNull('external_key')
                                ->get();

        foreach ($lIncidents as $oIncident) {
            $oLink = new IncidentExtSysLink();
            $oLink->incident_id = $oIncident->id;
            $oLink->external_system = "siie";
            $oLink->external_key = $oIncident->external_key;

            $oLink->save();

            $oIncident->is_external = true;
            $oIncident->save();
        }

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('external_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('incidents')->update(['is_external' => false]);

        IncidentExtSysLink::truncate();
    }
}
