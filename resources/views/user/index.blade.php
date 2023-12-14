@extends("theme.$theme.layout")
@section('title')
Usuarios
@endsection

@section("scripts")
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
<script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/indexFingerActivar.js")}}" type="text/javascript"></script>

<script src="{{asset("assets/pages/scripts/filter.js")}}" type="text/javascript"></script>


<script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
<script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
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
        
        $('.js-example-basic-multiple').select2();
    });
</script>
<script>
    $(document).ready(function() {
        $("#myTable").on('submit', '.form-eliminar', function() {
            event.preventDefault();
            const form = $(this);
            swal({
                title: '¿Está seguro que desea desactivar el registro?',
                text: "¡Esta acción se puede deshacer!",
                icon: 'warning',
                buttons: {
                    cancel: "Cancelar",
                    confirm: "Aceptar"
                },
            }).then((value) => {
                if (value) {
                    ajaxRequest(form);
                }
            });
        });
    
        function ajaxRequest(form) {
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(respuesta) {
                    if (respuesta.mensaje == "ok") {
                        form.parents('tr').remove();
                        Checador.notificaciones('El registro fue eliminado correctamente', 'Checador', 'success');
                    } else {
                        Checador.notificaciones('El registro no pudo ser eliminado, hay recursos usandolo', 'Checador', 'error');
                    }
                },
                error: function() {
    
                }
            });
        }
    });
</script>
<script>
    function GlobalData () {
        this.routeCopyUser = <?php echo json_encode(route('store_copyUser')); ?>;
    }
    var oData = new GlobalData();
    var oGui = new SGui();
</script>
<script src="{{ asset('assets/pages/scripts/user/vueUsers.js') }}"></script>
@endsection

@section('content')
<div class="row" id="usersApp">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                @switch($iFilter)
                @case (1)
                    <h3 class="box-title">Usuarios (activos)</h3>
                @break
                @case (2)
                    <h3 class="box-title">Usuarios (inactivos)</h3>
                @break
                @case (3)
                    <h3 class="box-title">Usuarios (todos)</h3>
                @break
            @endswitch
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:user"])
                <div class="row">
                    <div class="col-md-3 col-md-offset-9">
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{route('crear_usuario')}}" class="btn btn-block btn-success btn-sm">
                                    <i class="fa fa-fw fa-plus-circle"></i> Nuevo
                                </a>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <form action="{{ route('usuario') }}">
                                <input type="hidden" id="ifilter" name="ifilter">
                                <input type="hidden" id="efilter" name="efilter">
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
                                        @switch($eFilter)
                                            @case(1)
                                            <button onclick="filtroEmpleado(1)" type="submit" class="btn btn-secondary active">Con usuario</button>
                                            <button onclick="filtroEmpleado(2)" type="submit" class="btn btn-secondary">Sin usuario</button>
                                            
                                            @break
                                            @case(2)
                                            <button onclick="filtroEmpleado(1)" type="submit" class="btn btn-secondary">Con usuario</button>
                                            <button onclick="filtroEmpleado(2)" type="submit" class="btn btn-secondary active">Sin usuario</button>
                                            
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
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Empleado asociado</th>
                            <th>Estado</th>
                            <th class="width70"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $data)
                        <tr>
                            <td>{{$data->name}}</td>
                            <td>{{$data->email}}</td>
                            @if($data->employee == null)
                                <td>No definido</td>
                            @else
                                <td>{{$data->employee->name}}</td>
                            @endif
                            @if($data->is_delete == 0)
                                <td>Activo</td>
                            @else
                                <td>Inactivo</td>
                            @endif
                            <td>
                                <a href="{{route('editar_usuario', ['id' => $data->id])}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                @if($iFilter == 2)
                                <form action="{{route('activar_usuario', ['id' => $data->id])}}" class="d-inline form-activar" method="POST">
                                    @csrf @method("delete")
                                    <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Activar este registro">
                                        <i class="fa fa-fw fa-check-circle text-danger"></i>
                                    </button>
                                </form>
                                @elseif($iFilter == 1)
                                <form action="{{route('eliminar_usuario', ['id' => $data->id])}}" class="d-inline form-eliminar" method="POST">
                                    @csrf @method("delete")
                                    <button type="submit" class="btn-accion-tabla eliminar tooltipsC" title="Descactivar este registro">
                                        <i class="fa fa-fw fa-trash text-danger"></i>
                                    </button>
                                </form>
                                @endif
                                <a href="#" v-on:click="showCopyUser({{$data->id}}, '{{$data->name}}');" class="btn-accion-tabla tooltipsC" title="Copiar usuario en otro">
                                    <i class="fa fa-fw fa-files-o"></i>
                                </a>
                                
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('user.copyModal')
</div>
@endsection