@extends("theme.$theme.layout")
@section('title')
    Delegación de V.º B.º de prenómina
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
            @include('includes.form-error')
            @include('includes.mensaje')
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Delegación de V.º B.º de prenómina</h3>
                    @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:delegacionvobo"])
                    <div class="row">
                        <div class="col-md-5 col-md-offset-7">
                            <div class="row">
                                <div style="text-align: right" class="col-md-12">
                                    @if (! \App\SUtils\SPermissions::userHasRole(\Auth::user()->id, 15))
                                        <a href="{{ route('prepayrolldelegation.create') }}" class="btn btn-success btn-sm" id="btn_create">
                                            <i class="fa fa-plus-circle"></i> Nuevo
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-striped table-bordered table-hover" id="delegations_table_id">
                        <thead>
                            <tr>
                                <th>Per. pago</th>
                                <th>Núm. nómina</th>
                                <th>Usuario ausente</th>
                                <th>Usuario encargado</th>
                                <th>-</th>
                                <th>Creación</th>
                                <th>Modificación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lDelegations as $oDel)
                                <tr>
                                    <td>{{ $oDel->pay_way_id == \SCons::PAY_W_Q ? "Quincena" : "Semana" }}</td>
                                    <td>{{ str_pad($oDel->number_prepayroll, 2, "0", STR_PAD_LEFT).' - '.$oDel->year }}</td>
                                    <td>{{ $oDel->user_delegation_name }}</td>
                                    <td>{{ $oDel->user_delegated_name }}</td>
                                    <td>
                                        <form action="{{ route('prepayrolldelegation.delete', $oDel->id_delegation) }}" class="d-inline form-eliminar" method="POST">
                                            @csrf @method("delete")
                                            <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Eliminar este registro">
                                                <i class="fa fa-fw fa-trash text-danger"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>{{ $oDel->created_at." / ".$oDel->user_insert_name }}</td>
                                    <td>{{ $oDel->updated_at." / ".$oDel->user_update_name }}</td>
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
            $('#delegations_table_id').DataTable({
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