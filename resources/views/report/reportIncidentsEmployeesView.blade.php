@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
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
                <h3 class="box-title">Reporte incidencias empleados de {{$sStartDate}} a {{$sEndDate}}</h3>
                
                <div class="box-tools pull-right">
                    <a class="btn btn-success" href="{{$route}}">Nuevo reporte</a>
                </div>
            
            </div>
            <div class="box-body" >
                <div class="row">
                    <div class="col-md-12">
                        <table id="incidentsTable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Num empleado</th>
                                    <th>Empleado</th>
                                    <th>Fecha</th>
                                    <th>Incidencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lRows as $row)
                                        @switch($row->incident_type)
                                            @case(1)
                                            @case(2)
                                            @case(3)
                                            @case(4)
                                            @case(5)
                                            @case(6)
                                            @case(20)
                                                <tr>
                                                    <td style="background-color: #FF8A80">{{$row->num_employee}}</td>
                                                    <td style="background-color: #FF8A80">{{$row->employee}}</td>
                                                    <td style="background-color: #FF8A80">{{$row->date}}</td>
                                                    <td style="background-color: #FF8A80">{{$row->incident}}</td>
                                                </tr>
                                                @break
                                            @case(8)
                                            @case(9)
                                            @case(18)
                                                <tr>
                                                    <td style="background-color: #80D8FF">{{$row->num_employee}}</td>
                                                    <td style="background-color: #80D8FF">{{$row->employee}}</td>
                                                    <td style="background-color: #80D8FF">{{$row->date}}</td>
                                                    <td style="background-color: #80D8FF">{{$row->incident}}</td>
                                                </tr>
                                                @break
                                            @case(10)
                                            @case(11)
                                            @case(16)
                                                <tr>
                                                    <td style="background-color: #EA80FC">{{$row->num_employee}}</td>
                                                    <td style="background-color: #EA80FC">{{$row->employee}}</td>
                                                    <td style="background-color: #EA80FC">{{$row->date}}</td>
                                                    <td style="background-color: #EA80FC">{{$row->incident}}</td>
                                                </tr>
                                                @break
                                            @case(7)
                                            @case(12)
                                            @case(13)
                                            @case(17)
                                            @case(19)
                                                <tr>
                                                    <td style="background-color: #B2FF59">{{$row->num_employee}}</td>
                                                    <td style="background-color: #B2FF59">{{$row->employee}}</td>
                                                    <td style="background-color: #B2FF59">{{$row->date}}</td>
                                                    <td style="background-color: #B2FF59">{{$row->incident}}</td>
                                                </tr>
                                                @break
                                            @case(14)
                                            @case(15)
                                                <tr>
                                                    <td style="background-color: #FFD180">{{$row->num_employee}}</td>
                                                    <td style="background-color: #FFD180">{{$row->employee}}</td>
                                                    <td style="background-color: #FFD180">{{$row->date}}</td>
                                                    <td style="background-color: #FFD180">{{$row->incident}}</td>
                                                </tr>
                                                @break
                                            @default
                                            <tr>
                                                <td>{{$row->num_employee}}</td>
                                                <td>{{$row->employee}}</td>
                                                <td>{{$row->date}}</td>
                                                <td>{{$row->incident}}</td>
                                            </tr>
                                        @endswitch
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection

@section("scripts")

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
            $('#incidentsTable').DataTable({
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
                "order": [[ 1, 'asc' ]],
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
                    ],
                "drawCallback": function ( settings ) {
                    var api = this.api();
                    var rows = api.rows( {page:'current'} ).nodes();
                    var last=null;
        
                    api.column(1, {page:'current'} ).data().each( function ( group, i ) {
                        if ( last !== group ) {
                            $(rows).eq( i ).before(
                                '<tr class="group"><td colspan="4" style="background-color: #9C9C9C;">'+group+'</td></tr>'
                            );
        
                            last = group;
                        }
                    } );
                }
            });    
        });
       </script>
@endsection