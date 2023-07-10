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
                <h3 class="box-title">Grupos de prenómina vs departamentos</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:grupoprendeptos"])
                <div class="row">
                    <div class="col-md-5 col-md-offset-7">
                        <div class="row">
                            <div style="text-align: right" class="col-md-12">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Departamento</th>
                            <th>Grupo prenómina</th>
                            <th>Usr encargado</th>
                            <th>Seleccione nuevo grupo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lDepartments as $dept)
                            <tr>
                                <td>{{ $dept->name }}</td>
                                <td>{{ $dept->group_name }}</td>
                                <td>{{ $dept->gr_titular }}</td>
                                <td>
                                    <form action="{{ route('cambiar_grupo_dept') }}" method="post">
                                        @csrf
                                        <div style="white-space: nowrap;">
                                            <select name="new_group" class="form-select form-select-sm">
                                                <option value="0" {{ $dept->id_group == null ? "selected" : "" }}>NINGUNO</option>
                                                @foreach ($groups as $group)
                                                    <option value="{{ $group->id_group }}" {{ $group->id_group == $dept->id_group ? "selected" : "" }}>
                                                        {{ $group->group_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input value="{{ $dept->id_department }}" type="hidden" name="dept_id">
                                            <button class="btn btn-success btn-sm" type="submit">
                                                <span><i class="fa fa-check-circle" aria-hidden="true"></i></span>
                                            </button>
                                        </div>
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
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    
<script>
    $('#myTable').DataTable({
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
        "columnDefs": [
            { "searchable": false, "targets": 3 }
        ],
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
</script>
@endsection