@extends("theme.$theme.layout")
@section('title')
Puestos nominas
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/datatable/indexFingerActivar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/filter.js")}}" type="text/javascript"></script>

<script>
    $(document).ready( function () {
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
            "dom": 'Bfrtip',
            "lengthMenu": [
                [ 10, 25, 50, 100, -1 ],
                [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
            ],
            "buttons": [
                'pageLength',
                { extend: 'copy', text: 'Copiar'}, 'csv', 'excel', { extend: 'print', text: 'Imprimir'}
                ]
        });
    });
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                @switch($iFilter)
                    @case (1)
                        <h3 class="box-title">Puestos nominas (activos)</h3>
                    @break
                    @case (2)
                        <h3 class="box-title">Puestos nominas (inactivos)</h3>
                    @break
                    @case (3)
                        <h3 class="box-title">Puestos nominas (todos)</h3>
                    @break
                @endswitch
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:puestos"])
                <div class="row">
                    <div class="col-md-3 col-md-offset-9">
                        <div class="row">
                            <form action="{{ route('puesto_rh') }}">
                                <input type="hidden" id="ifilter" name="ifilter">
                                <div class="col-md-16">
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        @switch($iFilter)
                                            @case(1)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary active">Activos</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary">Inactivos</button>
                                            <button onclick="filter(3)" type="submit" class="btn btn-secondary">Todos</button>
                                            @break
                                            @case(2)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary">Activos</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary active">Inactivos</button>
                                            <button onclick="filter(3)" type="submit" class="btn btn-secondary">Todos</button>
                                            @break
                                            @case(3)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary">Activos</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary">Inactivos</button>
                                            <button onclick="filter(3)" type="submit" class="btn btn-secondary active">Todos</button>
                                            @break
                                        @endswitch
                                    </div>
                                </div>
                            </form>
                
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>Puesto nomina</th>
                            <th>Departamento nomina</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $data)
                        <tr>
                            <td>{{$data->job}}</td>
                            <td>{{$data->departmentRH->name}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection