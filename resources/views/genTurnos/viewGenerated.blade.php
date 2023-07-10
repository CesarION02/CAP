@extends("theme.$theme.layout")
@section('styles1')

@endsection
@section('title')
Generación de checadas
<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Generación espontanea de registros</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:generacionespontanea"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="col-md-3">
                <a href="{{route('registro_index_generate')}}" class="btn btn-success">Generar nuevo</a>
            </div>
            <br>
            <br>
            <table class="table table-striped table-bordered table-hover" id="myTable">
                <thead>
                    <tr>
                        <th colspan="6">{{$register->employee->name}}</th>
                    </tr>
                    <tr>
                        <th>turno</th>
                        <th>Horario</th>
                        <th>Fecha entrada</th>
                        <th>Entrada</th>
                        <th>Fecha salida</th>
                        <th>Salida</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($register->data as $week)
                        @foreach ($week as $day)
                            <tr>
                                <td>{{$day['turno']}}</td>
                                <td>{{$day['horario']}}</td>
                                <td>{{$day['fecha_entrada']}}</td>
                                <td>{{$day['entrada'] != '' ? $day['entrada'] : 'descanso'}}</td>
                                <td>{{$day['fecha_salida']}}</td>
                                <td>{{$day['salida'] != '' ? $day['salida'] : 'descanso'}}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            <div class="box-footer">
                <div class="" style="float: right;">
                    <button id="generar" class="btn btn-warning" type="" onclick="save();">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("scripts")
    
    
    
    
    
    
    
    
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script>
        $(document).ready( function () {
            $.fn.dataTable.moment('DD/MM/YYYY');
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
                    }
                },
                "bSort": false,
                "dom": 'Bfrtip',
                "lengthMenu": [
                    [ 15, 25, 50, 100, -1 ],
                    [ 'Mostrar 10', 'Mostrar 25', 'Mostrar 50', 'Mostrar 100', 'Mostrar todo' ]
                ],
                "buttons": [
                    'pageLength',
                    { extend: 'copy', text: 'Copiar'}, 'csv', 'excel', { extend: 'print', text: 'Imprimir'}
                ]
            });
        });
    </script>
    <script>
        function save() {
            var datas = <?php echo json_encode($register) ?>;
            $.ajax({
                type:'POST',
                url: '{{$ruta}}',
                data:{ register: datas, _token: '{{csrf_token()}}' },
                success: data => {
                    swal("Registro guardado correctamente");
                    window.location.href = data.redirectRoute;
                },
                error: data => {
                    swal("Error al guardar el registro");
                }
            });
        }
    </script>
@endsection