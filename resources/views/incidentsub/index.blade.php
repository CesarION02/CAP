@extends("theme.$theme.layout")
@section('title')
Subtipos de incidencia
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Subtipos incidencia para: {{ $typeName }}</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('subtipos_create', $idType) }}" class="btn btn-block btn-success btn-sm">
                        <i class="fa fa-fw fa-plus-circle"></i> Nuevo
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="table_data">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th style="text-align: center">Por default</th>
                            <th style="text-align: center">Activo</th>
                            <th style="text-align: center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lSubTypes as $oSubType)
                            <tr>
                                <td>
                                    {{ $oSubType->name }}
                                </td>
                                <td style="text-align: center">
                                    {{ $oSubType->is_default ? "SÍ" : "NO" }}
                                </td>
                                <td style="text-align: center">
                                    @if ($oSubType->is_delete)
                                        <i class="fa fa-times-circle text-danger" aria-hidden="true"></i>
                                    @else
                                        <i class="fa fa-check-circle text-success" aria-hidden="true"></i>
                                    @endif
                                </td>
                                <td style="text-align: center">
                                    <a href="{{ route('subtipos_edit', $oSubType->id_sub_incident) }}" title="Editar subtipo">
                                        <i class="fa fa-pencil-square-o text-info" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{route('subtipos_delete', $oSubType->id_sub_incident) }}" class="d-inline form-eliminar" method="POST">
                                        @csrf @method("delete")
                                        <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Eliminar o activar este registro">
                                            @if ($oSubType->is_delete)
                                                <i class="fa fa-check text-success" aria-hidden="true"></i>
                                            @else
                                                <i class="fa fa-times text-danger" aria-hidden="true"></i>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
<script src="{{ asset("assets/pages/scripts/admin/index.js") }}" type="text/javascript"></script>
<script src="{{ asset("dt/nv/datatables.js") }}" type="text/javascript"></script>

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