<?php

namespace App\Http\Controllers;

use App\Models\subtypeincident;
use App\Models\typeincident;
use Illuminate\Http\Request;

class SubTypeIncidentController extends Controller
{
    /**
     * Lista los tipos de incidencia
     * 
     * @param Request $request
     * 
     * @return \Illuminate\View\View
     */
    public function indexIncidentTypes()
    {
        $lIncidentTypes = \DB::table('type_incidents')->get();
        $updateAttrRoute = route('actualizar_atributo');

        foreach ($lIncidentTypes as $oIncType) {
            $oIncType->subTypesRoute = route('subtipos_index', $oIncType->id);
            $oIncType->editRoute = route('editar_tipoincidente', $oIncType->id);
            $oIncType->deleteRoute = route('eliminar_tipoincidente', $oIncType->id);
        }

        return view('incidentsub.typesindex')->with('lIncidentTypes', $lIncidentTypes)
                                            ->with('updateAttrRoute', $updateAttrRoute);
    }

    /**
     * Lista los subtipos de incidencia de un tipo de incidencia
     * 
     * @param Request $request
     * @param mixed $idType
     * 
     * @return \Illuminate\View\View
     */
    public function indexIncidentSubTypes(Request $request, $idType)
    {
        $lSubTypes = \DB::table('type_sub_incidents')
                            ->where('incident_type_id', $idType)
                            ->get();

        $oIncType = typeincident::find($idType);
        $typeName = $oIncType->name;

        return view('incidentsub.index')->with('lSubTypes', $lSubTypes)
                                        ->with('typeName', $typeName)
                                        ->with('idType', $idType);
    }

    /**
     * Muestra el formulario para crear un subtipo de incidencia
     * 
     * @param Request $request
     * @param mixed $idType
     * 
     * @return \Illuminate\View\View
     */
    public function create(Request $request, $idType)
    {
        $oIncType = typeincident::find($idType);
        $typeName = $oIncType->name;

        return view('incidentsub.create')->with('typeName', $typeName)
                                        ->with('idType', $idType);
    }

    /**
     * Guarda en la base de datos un subtipo de incidencia
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $oSubType = new subtypeincident();

        $oSubType->name = $request->subtype_name;
        $oSubType->is_default = isset($request->is_default) && !is_null($request->is_default) && $request->is_default;
        $oSubType->is_delete = false;
        $oSubType->incident_type_id = $request->incident_type; 

        $oSubType->save();

        return redirect()->route('subtipos_index', $oSubType->incident_type_id)->with('mensaje', 'Subtipo de incidencia creado con éxito');
    }

    /**
     * Muestra el formulario para editar un subtipo de incidencia
     * 
     * @param Request $request
     * @param mixed $id
     * @return \Illuminate\View\View
     */
    public function edit(Request $request, $id)
    {
        $oSubType = subtypeincident::find($id);
        $oIncType = typeincident::find($oSubType->incident_type_id);
        $typeName = $oIncType->name;

        return view('incidentsub.edit')->with('oSubType', $oSubType)
                                        ->with('typeName', $typeName)
                                        ->with('idType', $oSubType->incident_type_id);
    }

    /**
     * Actualiza un subtipo de incidencia
     * 
     * @param Request $request
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $oSubType = subtypeincident::find($id);

        $oSubType->name = $request->subtype_name;
        $oSubType->is_default = isset($request->is_default) && !is_null($request->is_default) && $request->is_default;

        $oSubType->save();

        return redirect()->route('subtipos_index', $oSubType->incident_type_id)->with('mensaje', 'Subtipo de incidencia actualizado con éxito');
    }

    /**
     * Elimina o activa un subtipo de incidencia
     * 
     * @param Request $request
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request, $id)
    {
        $oSubType = subtypeincident::find($id);

        $oSubType->is_delete = ! $oSubType->is_delete;

        $oSubType->save();

        return redirect()->route('subtipos_index', $oSubType->incident_type_id)->with('mensaje', 'Subtipo de incidencia eliminado con éxito');
    }
}
