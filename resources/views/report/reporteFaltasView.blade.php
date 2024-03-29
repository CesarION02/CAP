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


@section('content')
<div class="row" id="faltasReport">
<!-- Modal -->
<div id="fmodal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@{{Employee}}</h4>
            </div>
            <div class="modal-body">
                <ol id="recipient">
                    <li v-for="falta in lfaltas">@{{falta.fechaI}}</li>
                </ol>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Reporte ausentismo</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:reporteausentismo"])
                <div class="box-tools pull-right">
                </div>
            
            </div>
            <div class="col-md-2" style="float: left; width: 150px;">
                <a class="btn btn-success" href="{{route('reporteFaltas')}}">Nuevo reporte</a>
            </div>
            <div class="col-md-2" style="float: left;">
                <select class="form-select" name="isActive" id="isActive" style="width: 126px; height: 34px;">
                    <option value="0" selected>Activos</option>
                    <option value="1">Inactivos</option>
                    <option value="2">Todos</option>
                </select>
            </div>
            <br>
            <br>
            <div class="box-body" >
                <div class="row">
                    <div class="col-md-12">
                        <table id="faltas_table" class="table table-condensed" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Empleado</th>
                                    <th>Num empleado</th>
                                    <th style="min-width: 50px;">Fecha ingreso</th>
                                    <th>Activo</th>
                                    <th>Departamento</th>
                                    @foreach ($range as $r)
                                        <th>{{$r->nombre}}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($totEmployees as $d)
                                    <tr>
                                        <td>{{$d->empleado}}</td>
                                        <td>{{$d->num}}</td>
                                        <td>{{$d->admission}}</td>
                                        <td style="text-align: center;">{{$d->active}}</td>
                                        <td>{{$d->departamento}}</td>
                                        @foreach ($range as $r)
                                            <td>
                                                {{
                                                $data->where('empleado_id', $d->empleado_id)
                                                    ->whereBetween('fechaI', [
                                                        $calendarStart['year'].'-'.
                                                        $r->mes.'-01', 
                                                        $calendarStart['year'].'-'.
                                                        $r->mes.'-'.
                                                        cal_days_in_month(CAL_GREGORIAN,$r->mes,$calendarStart['year'])
                                                        ])->sortBy('fechaI')->unique('fechaI')->count()
                                                }}
                                                <!-- Trigger the modal with a button -->
                                                <a href="#" data-toggle="modal" data-target="#fmodal" 
                                                    v-on:click="setEmpl('{{$d->empleado}}',
                                                    {{$data->where('empleado_id', $d->empleado_id)
                                                        ->whereBetween('fechaI', [
                                                            $calendarStart['year'].'-'.
                                                            $r->mes.'-01', 
                                                            $calendarStart['year'].'-'.
                                                            $r->mes.'-'.
                                                            cal_days_in_month(CAL_GREGORIAN,$r->mes,$calendarStart['year'])
                                                            ])->sortBy('fechaI')->unique('fechaI')}})">
                                                    <span class="fa fa-calendar-o fa-1"></span>
                                                </a>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                            <?php $cadenaregreso = 'reporteFaltas/';?>
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
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/report/SFaltasReport.js")}}"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.print.min.js') }}"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            $.fn.dataTable.ext.search.push(
                function( settings, data, dataIndex ) {
                    let registerVal = parseInt( $('#isActive').val(), 10 );
                    let isActive = 0;

                    switch (registerVal) {
                        case 1:
                            isActive = parseInt( data[3] );
                            return isActive === 0;
                            
                        case 0:
                            isActive = parseInt( data[3] );
                            return ! (isActive === 0);

                        case 2:
                            return true;

                        default:
                            break;
                    }

                    return false;
                }
            );

            $.fn.dataTable.moment('DD/MM/YYYY');
            table = $('#faltas_table').DataTable({
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
                            extend: 'copy', text: 'copiar',
                        },
                        {
                            extend: 'csv',
                        },
                        {
                            extend: 'print', text: 'imprimir',
                        }
                    ]
            });

            $('#isActive').change( function() {
                table.draw();
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