<?php namespace App\SUtils;

use App\SUtils\SDateUtils;

class SPayrollDelegationUtils {

    /**
     * Retorna un arreglo con los id de los grupos de prenómina involucrados en todas las delegaciones
     *
     * @param int $idUser
     * @return array
     */
    public static function getGroupsAllDelegations($idUser)
    {
        $delegations = \DB::table('prepayroll_report_delegations AS d')
                            ->where('d.user_delegated_id', $idUser)
                            ->where('d.is_delete', false)
                            ->get();

        $aGroups = [];
        foreach ($delegations as $delegation) {
            $objInsertions = json_decode($delegation->json_insertions);
            $aGroups = array_merge($aGroups, $objInsertions->prepay_groups_user);
        }

        return array_unique($aGroups);
    }

    /**
     * Retorna un arreglo con los id de los grupos de prenómina involucrados en la delegación
     *
     * @param [type] $idUser
     * @param [type] $idDelegation
     * 
     * @return array
     */
    public static function getGroupsOfDelegation($idUser, $idDelegation)
    {
        $delegations = \DB::table('prepayroll_report_delegations AS d')
                            ->where('d.user_delegated_id', $idUser)
                            ->where('d.id_delegation', $idDelegation)
                            ->where('d.is_delete', false)
                            ->get();

        $aGroups = [];
        foreach ($delegations as $delegation) {
            $objInsertions = json_decode($delegation->json_insertions);
            $aGroups = array_merge($aGroups, $objInsertions->prepay_groups_user);
        }

        return array_unique($aGroups);
    }

    /**
     * Retorna un objeto correspondiente a una delegación
     *
     * @param integer $idUser
     * 
     * @return \stdClass
     */
    public static function getDelegationsPayrolls($idUser)
    {
        $delegations = \DB::table('prepayroll_report_delegations AS d')
                            ->where('d.user_delegated_id', $idUser)
                            ->where('d.is_delete', false)
                            ->where('pay_way_id', \SCons::PAY_W_Q)
                            ->get();

        $biweeks = [];
        foreach ($delegations as $delegation) {
            $qCut = new \stdClass();
            
            $dates = SDateUtils::getDatesOfPayrollNumber($delegation->number_prepayroll, $delegation->year, \SCons::PAY_W_Q);
            $qCut->start_date = $dates[0];
            $qCut->end_date = $dates[1];
            $qCut->year = $delegation->year;
            $qCut->number = $delegation->number_prepayroll;

            $biweeks[] = $qCut;
        }

        $delegations = \DB::table('prepayroll_report_delegations AS d')
                            ->where('d.user_delegated_id', $idUser)
                            ->where('d.is_delete', false)
                            ->where('pay_way_id', \SCons::PAY_W_S)
                            ->get();

        $weeks = [];
        foreach ($delegations as $delegation) {
            $qCut = new \stdClass();
            
            $dates = SDateUtils::getDatesOfPayrollNumber($delegation->number_prepayroll, $delegation->year, \SCons::PAY_W_S);
            $qCut->start_date = $dates[0];
            $qCut->end_date = $dates[1];
            $qCut->year = $delegation->year;
            $qCut->number = $delegation->number_prepayroll;

            $weeks[] = $qCut;
        }

        $response = new \stdClass();
        $response->biweeks = $biweeks;
        $response->weeks = $weeks;

        return $response;
    }
}