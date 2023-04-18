@extends("theme.$theme.layout")
@section('title')
    Tipos de Incidencias
@endsection

@section('styles1')
<style>
/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

span.nobr { white-space: nowrap; }
</style>
@endsection

@section('content')
<div class="row" id="subIncidentsApp">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">
                    Gestión de tipos de incidencia
                </h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:nolaborables"])
                <div class="row">
                    
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="table_data">
                    <thead>
                        <tr>
                            <th>Tipo incidencia</th>
                            <th>Es acuerdo</th>
                            <th>Paga bonos</th>
                            <th>Alta en CAP</th>
                            <th>Se paga</th>
                            <th>Tiene subtipos</th>
                            <th>--</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(oIncType, index) in lIncidentTypes">
                            <td>@{{ oIncType.name }}</td>                        
                            <td>
                                <!-- Rounded switch -->
                                <label class="switch">
                                    <input type="checkbox" v-model="oIncType.is_agreement" 
                                        v-on:change="onToggleChange(oIncType.id, 'is_agreement', oIncType.is_agreement)">
                                    <span class="slider round"></span>
                                </label>
                            </td>                        
                            <td>
                                <!-- Rounded switch -->
                                <label class="switch">
                                    <input type="checkbox" v-model="oIncType.is_allowed" 
                                    v-on:change="onToggleChange(oIncType.id, 'is_allowed', oIncType.is_allowed)">
                                    <span class="slider round"></span>
                                </label>
                            </td>                        
                            <td>
                                <!-- Rounded switch -->
                                <label class="switch">
                                    <input type="checkbox" v-model="oIncType.is_cap_edit" 
                                    v-on:change="onToggleChange(oIncType.id, 'is_cap_edit', oIncType.is_cap_edit)">
                                    <span class="slider round"></span>
                                </label>
                            </td>                        
                            <td>
                                <!-- Rounded switch -->
                                <label class="switch">
                                    <input type="checkbox" v-model="oIncType.is_payable" 
                                    v-on:change="onToggleChange(oIncType.id, 'is_payable', oIncType.is_payable)">
                                    <span class="slider round"></span>
                                </label>
                            </td>                        
                            <td>
                                <!-- Rounded switch -->
                                <label class="switch">
                                    <input type="checkbox" v-model="oIncType.has_subtypes" 
                                    v-on:change="onToggleChange(oIncType.id, 'has_subtypes', oIncType.has_subtypes)">
                                    <span class="slider round"></span>
                                </label>
                            </td>                      
                            <td>
                                <span class="nobr">
                                    {{-- Editar tipo de incidencia --}}
                                    <a :href="oIncType.editRoute" type="button" title="Editar tipo de incidencia">
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                    </a>
                                    {{-- Catálogo de subtipos de incidencia --}}
                                    <a v-if="oIncType.has_subtypes" :href="oIncType.subTypesRoute" type="button" title="Editar subtipos de incidencia">
                                        <i class="fa fa-eye text-info" aria-hidden="true"></i>
                                    </a>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
<script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
<script src="{{ asset('dt/jszip.min.js') }}"></script>
<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
<script src="{{ asset('dt/buttons.print.min.js') }}"></script>
<script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>

<script>
    function GlobalData () {
        this.lIncidentTypes = <?php echo json_encode($lIncidentTypes) ?>;
        this.updateAttrRoute = <?php echo json_encode($updateAttrRoute) ?>;
    }

    var oGlobalData = new GlobalData();
</script>

<script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>

<script>
    var oGui = new SGui();
</script>

<script src="{{ asset("assets/pages/scripts/incidentsSub/SVueSubIncidents.js")}}" type="text/javascript"></script>

<script>
    $(document).ready( function () {
        $('#table_data').DataTable({
            "language": {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            },
            "colReorder": true,
            "dom": 'Bfrtip',
            "lengthMenu": [
                [ 10, 25, 50, 100, -1 ],
                [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
            ],
            "buttons": [
                        'pageLength',
                    {
                        extend: 'copy',
                        text: 'Copiar'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel'
                    },
                    {
                        extend: 'print',
                        text: 'Imprimir'
                    }
                ]
        });
    });
</script>
@endsection