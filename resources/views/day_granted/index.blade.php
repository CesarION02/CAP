@extends("theme.$theme.layout")
@section('title')
Incidencias
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/filter.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
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
<script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>

<script>
    $(document).ready( function () {
        $.fn.dataTable.moment('DD/MM/YYYY');

        $('#tabla-data').DataTable({
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
    $(function() {
        

        function cb(start, end) {
            $('#reportrange span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'));
            document.getElementById("start-date").value = start.format('YYYY-MM-DD');
            document.getElementById("end-date").value = end.format('YYYY-MM-DD');
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            showDropdowns: true,
            ranges: { 
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Este año': [moment().startOf('year'), moment().endOf('year')],
                'Año pasado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        }, cb);

        cb(start, end);
    });

    $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        document.getElementById("start-date").value = picker.startDate.format('YYYY-MM-DD');
        document.getElementById("end-date").value = picker.endDate.format('YYYY-MM-DD');
    });
</script>
@endsection
@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">
                    @switch($iFilter)
                        @case(1)
                            Días festivos trabajados otorgados
                            @break
                        @case(2)
                            Días festivos trabajados por otorgar
                            @break
                        @default
                            Días festivos otorgados y pendientes de otorgar
                    @endswitch
                </h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:nolaborables"])
                <div class="row">
                    <div class="col-md-3 col-md-offset-9">
                        <div class="row">
                            <div class="col-md-12">
                            
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <form action="{{ route('diaotorgado') }}">
                                <input type="hidden" id="ifilter" name="ifilter" value="{{$iFilter}}">
                                <div class="col-md-16">
                                    <div class="btn-group" role="group" aria-label="Basic example">
                                        @switch($iFilter)
                                            @case(1)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary active">Otorgados</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary">No otorgados</button>
                                            <button onclick="filter(3)" type="submit" class="btn btn-secondary">Todos</button>
                                            @break
                                            @case(2)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary">Otorgados</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary active">No otorgados</button>
                                            <button onclick="filter(3)" type="submit" class="btn btn-secondary">Todos</button>
                                            @break
                                            @case(3)
                                            <button onclick="filter(1)" type="submit" class="btn btn-secondary">Otorgados</button>
                                            <button onclick="filter(2)" type="submit" class="btn btn-secondary">No otorgados</button>
                                            <button onclick="filter(3)" type="submit" class="btn btn-secondary active">Todos</button>
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
                <table class="table table-striped table-bordered table-hover" id="tabla-data">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Empleado</th>
                            <th>Día festivo</th>
                            <th>Comentarios</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $data)
                        <tr>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($data->date)}}</td>
                            <td>{{$data->nameEmp}}</td>
                            <td>{{$data->nameholi}}</td>
                            <td>{{$data->comentarios}}</td>
                            
                            <td>
                                <a href="{{route('editar_diaotorgado', ['id' => $data->id])}}" class="btn-accion-tabla tooltipsC" title="Modificar este registro">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection