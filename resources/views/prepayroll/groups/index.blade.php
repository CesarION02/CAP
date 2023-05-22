@extends("theme.$theme.layout")
@section('title')
    Grupos de prenómina
@endsection

@section('styles1')
    <style>
        .th {
            font-size: 10pt;
        }
        .tr {
            font-size: 5pt;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @include('includes.mensaje')
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Grupos de prenómina</h3>
                    @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php"])
                    <div class="row">
                        <div class="col-md-5 col-md-offset-7">
                            <div class="row">
                                <div style="text-align: right" class="col-md-12">
                                    <a href="{{ route('create_prepayroll_group') }}" class="btn btn-success btn-sm" id="btn_create">
                                        <i class="fa fa-plus-circle"></i> Nuevo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <table style="display: none" class="table table-striped table-bordered table-hover" id="prepayroll_groups_table_id">
                        <thead>
                            <tr>
                                <th>Grupo prenómina</th>
                                <th>Grupo prenómina padre</th>
                                <th>Usrs encargados</th>
                                <th>-</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lGroups as $group)
                                <tr>
                                    <td>{{ $group->group_name }}</td>
                                    <td>{{ $group->father_group_name }}</td>
                                    <td>{{ $group->head_users }}</td>
                                    <td>
                                        <a href="{{ route('edit_prepayroll_group', $group->id_group) }}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                            <i class="fa fa-fw fa-pencil"></i>
                                        </a>
                                        <form action="{{route('destroy_prepayroll_group', ['id' => $group->id_group])}}" class="d-inline form-eliminar" method="POST">
                                            @csrf @method("delete")
                                            <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Eliminar este registro">
                                                <i class="fa fa-fw fa-trash text-danger"></i>
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
    <script src="{{ asset("assets/pages/scripts/admin/datatable/indexFingerActivar.js")}}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/filter.js")}}" type="text/javascript"></script>
    
    
    
	
	
	
	
    

    <script>
        $(document).ready( function () {
            // $.fn.dataTable.moment('DD/MM/YYYY');
            $('#prepayroll_groups_table_id').DataTable({
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
                "order": [[ 1, 'asc' ]],
                "colReorder": true,
                "initComplete": function() { 
                        $("#prepayroll_groups_table_id").show();
                },
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
                        'csv', 
                        'excel', 
                        {
                            extend: 'print',
                            text: 'Imprimir'
                        }
                    ],
            });

        });


    </script>
@endsection