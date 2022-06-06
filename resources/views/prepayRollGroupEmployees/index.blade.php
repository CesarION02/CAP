@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
@endsection
@section('title')
Reporte faltas
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Grupos prenomina empleados</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <form id="form" action="{{ route('prepayroll_emp_grupo_generate') }}" method="post">
                @csrf
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-5">
                            <label for="tipo">Generar reporte por:</label>
                            <select data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" name="user" required> 
                                <option value=""></option>
                                @foreach ($lUsers as $user)
                                    @if ($user->id == $idUser)
                                        <option value="{{$user->id}}" selected>{{$user->name}}</option>
                                    @else
                                        <option value="{{$user->id}}">{{$user->name}}</option>                                    
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-check-label" for="bDirect">
                              Solo empleados directos
                            </label>
                            <input class="form-check-input" type="checkbox" name="bDirect" id="bDirect">
                          </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary">Consultar</button>
                        </div>
                    </div>
                    <br>
                    <br>
                    <table id="employeesTable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>Grupo empleado</th>
                                <th>Grupo departamento</th>
                                <th>departamento</th>
                                <th>empleado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lEmployees as $emp)
                                <tr>
                                    <td>{{$emp->group_name_employee}}</td>
                                    <td>{{$emp->group_name_depto}}</td>
                                    <td>{{$emp->department}}</td>
                                    <td>{{$emp->employee}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script>
        $(".chosen-select").chosen();
    </script>
    <script>
        $(document).ready(function() {
            let table = $('#employeesTable').DataTable({
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
                "scrollX": true,
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
            
            var checked = '<?php echo $bDirect; ?>';
            console.log(checked);
            if(checked == 1){
                document.getElementById('bDirect').checked = true;
            }
        });
    </script>
@endsection