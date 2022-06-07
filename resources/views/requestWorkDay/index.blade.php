@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{asset("assets/css/chosen.min.css")}}">
    <link rel="stylesheet" href="{{asset("assets/css/selectchosen.css")}}">
@endsection
@section('title')
Generación solicitud para presentarse a laborar
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Generación solicitud para presentarse a laborar</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            @csrf
            <div class="box-body">
                @if ($isAdmin)
                    <form id="form" action="{{ route('request_work_day_generate') }}" method="post">
                        @csrf
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
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary">Consultar</button>
                            </div>
                        </div>
                    </form>
                @endif
                <br>
                <br>
                <form id="formGeneratePDF" action="{{ route('request_work_day_getPDF') }}" method="post">
                    @csrf
                    <input type="hidden" value="{{$idUser}}" name="user_id">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="fecha">Fecha:</label>
                            <select id="fecha" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" name="fecha" required> 
                                <option value=""></option>
                                @foreach ($lSundays as $sun)
                                    <option value="{{$sun[1]}}">{{$sun[0]}} {{$sun[1]}} {{$sun[2]}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="turno">Turno:</label>
                            <select id="turno" data-placeholder="Selecciona opciones..." style="width: 60%" class="chosen-select" name="turno" required> 
                                <option value=""></option>
                                @foreach ($workshifts as $w)
                                    <option value="{{$w->id}}">{{$w->name}} - {{$w->entry}} a {{$w->departure}}</option>
                                @endforeach
                            </select>
                        </div>
                        <br>
                        <br>
                    </div>
                    <table id="employeesTable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>Grupo empleado</th>
                                <th>Grupo departamento</th>
                                <th>departamento</th>
                                <th>empleado</th>
                                <th>Generar solicitud</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lEmployees as $emp)
                                <tr>
                                    <td>{{$emp->group_name_employee}}</td>
                                    <td>{{$emp->group_name_depto}}</td>
                                    <td>{{$emp->department}}</td>
                                    <td>{{$emp->employee}}</td>
                                    <td style="text-align: center"><input class="form-check-input generateCheck" type="checkbox" value="{{$emp->employee_id}}" name="generateCheck[]" style="transform: scale(2);"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                    <br>
                    <div class="col-md-1" style="float:right;">
                        <button type="button" class="btn btn-primary" onclick="valthisform();">Generar</button>
                    </div>
                </form>
            </div>
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
        var oGui = new SGui();
    </script>
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
        });
    </script>
    <script>
        function valthisform()
        {
            var checkboxs=document.getElementsByClassName("generateCheck");
            var fecha = document.getElementById('fecha');
            var turno = document.getElementById('turno');
            var okay=false;
            for(var i=0,l=checkboxs.length;i<l;i++)
            {
                if(checkboxs[i].checked)
                {
                    okay=true;
                    break;
                }
            }

            if(!okay){
                oGui.showMessage('Error','Debe seleccionar al menos un empleado','error');
                return null;
            }

            if(fecha.value == "" || turno.value == ""){
                oGui.showMessage('Error','fecha y turno requerido','error');
                return null;
            }

            if(okay && fecha.value != "" && turno != ""){
                var form = document.getElementById('formGeneratePDF');
                form.submit();
                oGui.showLoading(10000);
            }

        }
    </script>
@endsection