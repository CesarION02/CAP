@extends("theme.$theme.layout")
@section('title')
Huellas digitales
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/datatable/indexFingerActivar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/botoncopiar.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/indexFinger.js")}}" type="text/javascript"></script>
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
                    @case(1)
                        <h3 class="box-title">Huellas digitales (activos)</h3>
                    @break
                    @case(2)
                        <h3 class="box-title">Huellas digitales (inactivos)</h3>
                    @break
                    @case(3)
                        <h3 class="box-title">Huellas digitales (todos)</h3>
                    @break
                @endswitch
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:huellas"])
                <div class="row">
                    <div class="col-md-3 col-md-offset-9">
                        <div class="row">
                            <div class="col-md-12">
                            
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <form action="{{ route('huellas') }}">
                                <input type="hidden" id="ifilter" name="ifilter" value="{{$iFilter}}">
                                <input type="hidden" id="ifilterH" name="ifilterH" value="{{$iFilterH}}">
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
                                <div>
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        @switch($iFilterH)
                                            @case(1)
                                            <button onclick="filtroHuella(1)" type="submit" class="btn btn-secondary active">Con huella</button>
                                            <button onclick="filtroHuella(2)" type="submit" class="btn btn-secondary">Sin huella</button>
                                            
                                            @break
                                            @case(2)
                                            <button onclick="filtroHuella(1)" type="submit" class="btn btn-secondary">Con huella</button>
                                            <button onclick="filtroHuella(2)" type="submit" class="btn btn-secondary active">Sin huella</button>
                                            
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
                            <th>Nombre empleado</th>
                            <th>Número empleado</th>
                            <th>Política registro</th>
                            <th>Huella digital</th>
                            <th>Estado</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $contador = 0; ?>
                        @foreach ($employees as $employee)
                            <?php $contador = $contador+1;?>
                            @if($iFilterH == 1)
                                @if($employee->fingerprint != null)
                                    <tr>
                                        <td id = <?php echo "nombre".$contador; ?> >{{$employee->nameEmployee}}</td>
                                        <td>{{$employee->num}}</td>
                                        <td>{{$employee->way}}</td>
                                        
                                            @if($employee->fingerprint != null)
                                                <td style="background-color:green"><font color="white">Registrada</font></td>
                                            @else
                                                <td style="background-color:red"><font color="white">No registrada</font></td>
                                            @endif
                                        @if($employee->is_delete == 0)
                                            <td>Activo</td>
                                        @else
                                            <td>Inactivo</td>
                                        @endif
                                        <td>
                                            <button onclick="copiarAlPortapapeles(<?php echo "nombre".$contador;?>)" class="btn-accion-tabla eliminar tooltipsC" >
                                                <i class="fa fa-fw fa-files-o"></i>    
                                            </button>
                                            @if($rol == 1 || $rol == 7)
                                                <a href="{{route('editarhuella', ['id' => $employee->idEmployee])}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                                    <i class="fa fa-fw fa-pencil"></i>
                                                </a>
                                            @endif
                                            @if($iFilter == 2)
                                            <form action="{{route('activar', ['id' => $employee->idEmployee])}}" class="d-inline form-activar" method="POST">
                                                @csrf @method("delete")
                                                <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Activar este registro">
                                                    <i class="fa fa-fw fa-check-circle text-danger"></i>
                                                </button>
                                            </form>
                                            @elseif($iFilter == 1)
                                            <form action="{{route('desactivar', ['id' => $employee->idEmployee])}}" class="d-inline form-eliminar" method="POST">
                                                @csrf @method("delete")
                                                <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Desactivar este registro">
                                                    <i class="fa fa-fw fa-times-circle text-danger"></i>
                                                </button>
                                            </form>
                                            @endif
                                            
                                        </td>
                                    </tr>
                                @endif
                            @else
                                @if($employee->fingerprint == null)
                                    <tr>
                                        <td>{{$employee->nameEmployee}}</td>
                                        <td>{{$employee->num}}</td>
                                        <td>{{$employee->way}}</td>
                                        
                                            @if($employee->fingerprint != null)
                                                <td style="background-color:green"><font color="white">Registrada</font></td>
                                            @else
                                                <td style="background-color:red"><font color="white">No registrada</font></td>
                                            @endif
                                        @if($employee->is_delete == 0)
                                            <td>Activo</td>
                                        @else
                                            <td>Inactivo</td>
                                        @endif
                                        <td>
                                            <button onclick="copiarAlPortapapeles(<?php echo "nombre".$contador;?>)" class="btn-accion-tabla eliminar tooltipsC" >
                                                <i class="fa fa-fw fa-files-o"></i>    
                                            </button>
                                            @if($rol == 1 || $rol == 7)
                                                <a href="{{route('editarhuella', ['id' => $employee->idEmployee])}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                                    <i class="fa fa-fw fa-pencil"></i>
                                                </a>
                                            @endif
                                            @if($iFilter == 2)
                                            <form action="{{route('activar', ['id' => $employee->idEmployee])}}" class="d-inline form-activar" method="POST">
                                                @csrf @method("delete")
                                                <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Activar este registro">
                                                    <i class="fa fa-fw fa-check-circle text-danger"></i>
                                                </button>
                                            </form>
                                            @elseif($iFilter == 1)
                                            <form action="{{route('desactivar', ['id' => $employee->idEmployee])}}" class="d-inline form-eliminar" method="POST">
                                                @csrf @method("delete")
                                                <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Desactivar este registro">
                                                    <i class="fa fa-fw fa-times-circle text-danger"></i>
                                                </button>
                                            </form>
                                            @endif
                                            
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection