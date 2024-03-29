@extends("theme.$theme.layout")

@section('styles1')
<link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
<link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
<style>
    tr {
        font-size: 70%;
    }
    span.nobr { white-space: nowrap; }
</style>
@endsection
@section('title')
    Turno
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/funciones.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
<script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
<script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.print.min.js') }}"></script>
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>

    <script>
        //Get the button:
        mybutton = document.getElementById("myBtn");
        theNewButton = document.getElementById("newButton");

        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
            if (document.body.scrollTop > 10 || document.documentElement.scrollTop > 10) {
                mybutton.style.display = "block";
                theNewButton.style.display = "block";
            } else {
                mybutton.style.display = "none";
                theNewButton.style.display = "none";
            }
        }

        // When the user clicks on the button, scroll to the top of the document
        function topFunction() {
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        }
    </script>
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
                <h3 class="box-title">Reporte número de registros</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:reporterevisionnumerochecadas"])
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered table-hover " id="myTable">
                    <thead>
                        
                        <tr>
                            <th>Empleado</th>
                            <th>Programado</th>
                            @for($i = 0 ; $diff >= $i ; $i++)

                                <th><?php echo date("d-m-Y",strtotime($inicio."+ ".$i." days")); ?></th>
                                <th><?php echo date("d-m-Y",strtotime($inicio."+ ".$i." days")); ?></th>
                            @endfor
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            @for($i = 0 ; $diff >= $i ; $i++)

                                <th>Entradas</th>
                                <th>Salidas</th>
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
                                
                                <td>{{$lRows[$i]->num_entrada}}</td>
                                <td>{{$lRows[$i]->num_salida}}</td>
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
                                <td>{{$lRows1[$i]->num_entrada}}</td>
                                <td>{{$lRows1[$i]->num_salida}}</td>
                                <?php $i++;?>
                            @endfor
                            </tr>
                       @endfor
                    </tbody>
                </table>
                <button onclick="topFunction()" id="myBtn" title="Ir arriba">Ir arriba</button>
                <a href="{{ route('reporte_numero_registros') }}" target="_blank" id="newButton" title="Nuevo reporte">Nuevo reporte</a>
            </div>
        </div>
    </div>
</div>
@endsection