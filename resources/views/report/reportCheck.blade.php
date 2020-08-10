@extends("theme.$theme.layout")
@section('title')
Reporte revisión
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
                        'pageLength', 'copy', 'csv', 'excel', 'print'
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
                <h3 class="box-title"></h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered table-hover " id="checks_table">
                    <thead>
                        
                        <tr>
                            <th>Empleado</th>
                            <th>Programación</th>
                            @for($i = 0 ; $diff >= $i ; $i++)

                                <th><?php echo date("d-m-Y",strtotime($inicio."+ ".$i." days")); ?></th>
    
                            @endfor
                        </tr>
                        
                    </thead>
                    <tbody>
                        <?php $i = 0;?> 
                       @for($j = 0 ; $numEmpleados > $j ; $j++)
                            <tr>
                            <td>{{$lRows[$i]->nameEmployee}}</td>
                            @if($programado[$j] == true)
                                <td>Programado</td>
                            @else
                                <td>No programado</td>
                            @endif
                            @for($x = 0 ; $diff >= $x ; $x++)
                                @if($lRows[$i]->entrada == true)
                                    <td>Registro</td>
                                @else
                                    <td>No registro</td>
                                @endif

                                <?php $i++;?>
                            @endfor
                            </tr>
                       @endfor
                       <?php $i = 0;?> 
                       @for($j = 0 ; $numEmpleados1 > $j ; $j++)
                            <tr>
                            <td>{{$lRows1[$i]->nameEmployee}}</td>
                            @if($programado1[$j] == true)
                                <td>Programado</td>
                            @else
                                <td>No programado</td>
                            @endif
                            @for($x = 0 ; $diff >= $x ; $x++)
                                @if($lRows1[$i]->entrada == true)
                                    <td>Registro</td>
                                @else
                                    <td>No registrado</td>
                                @endif

                                <?php $i++;?>
                            @endfor
                            </tr>
                       @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection