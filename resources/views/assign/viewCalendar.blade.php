@extends("theme.$theme.layout")
@section('title')
Horario específico
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/funciones.js")}}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.print.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#checks_table').DataTable({
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
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Horario fecha exacta</h3>
                <div class="box-tools pull-right">
                    <a href="{{route('fecha_especifica')}}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-striped table-bordered table-hover" id="checks_table">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            @for($i = 0 ; $diff >= $i ; $i++)

                                <th><?php echo date("d-m-Y",strtotime($inicio."+ ".$i." days")); ?></th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                            @if ($info == 0)
                                <th colspan="8">No hay registros para esas fechas</th>
                            @else
                                @foreach ($calendario as $elem)
                                    <tr>
                                        <th>{{ $elem[0]->Nombre }}</th>
                                        @foreach ($elem as $day)
                                            <td>{{ ($day->Entrada == null ? '' : $day->Entrada).' - '.($day->Salida == null ? '' : $day->Salida) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


