@extends("theme.$theme.layout")
@section('styles1')
    
    <link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
    <style>
        tr {
            font-size: 70%;
        }
        span.nobr { white-space: nowrap; }
    </style>
@endsection


@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte incidencias</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:reporteincidencias"])
                <div class="box-tools pull-right">
                </div>
            
            </div>
            <div class="box-body" >
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed" style="width:100%">
                            <thead>
                                <tr>
                                    @if($tipo == 2)
                                        <th>Empleado
                                        </th>
                                    @else
                                        <th>Departamento nómina</th>
                                        <th>Empleado</th>
                                    @endif
                                    <th>Fecha inicio</th>
                                    <th>Fecha fin</th>
                                    <th>Incidencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0 ; count($datas) > $i ; $i++)
                                        <tr>
                                            @if($tipo == 2)
                                                <td>{{ $datas[$i]->empleado }}</td>
                                            @else
                                                <td>{{ $datas[$i]->departamento }}</td>
                                                <td>{{ $datas[$i]->empleado }}</td>
                                            @endif
                                            
                                            <td>{{ $datas[$i]->fechaI }}</td>
                                            <td>{{ $datas[$i]->fechaF }}</td>
                                            <td>{{ $datas[$i]->tipo }}</td>

                                        </tr>
                                @endfor
                            </tbody>
                            <?php $cadenaregreso = 'reporteIncidencias/';?>
                            <button onclick="topFunction()" id="myBtn" title="Ir arriba">Ir arriba</button>
                            <a href="{{ $cadenaregreso }}"  id="newButton" title="Nuevo reporte">Nuevo reporte</a>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection

@section("scripts")

    
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>

        



    <script>
        $(document).ready(function() {
            $.fn.dataTable.moment('DD/MM/YYYY');
            $('#delays_table').DataTable({
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
                "iDisplayLength": -1,
                "buttons": [
                        'pageLength',
                        {
                            extend: 'excel', 
                            
                        },
                        {
                            extend: 'copy', text: 'copiar'
                            
                        },
                        {
                            extend: 'csv',
                            
                        },
                        {
                            extend: 'print', text: 'imprimir'
                            
                        }
                    ]
            });
        });
    </script>

    <script>
        //Get the button:
        mybutton = document.getElementById("myBtn");
        theNewButton = document.getElementById("newButton");

        // When the user scrolls down 20px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
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
@endsection