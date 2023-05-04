@extends("theme.$theme.layout")
@section('title')
Incidencias
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection

@section("scripts")
<script src="{{asset("assets/pages/scripts/admin/index.js")}}" type="text/javascript"></script>
<script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>







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
        var year = moment(<?php echo json_encode($year) ?>);

        function cb(year) {
            $('#reportrange span').html(year.format('YYYY'));
            document.getElementById("year").value = year.format('YYYY');
        }

        $('#reportrange').daterangepicker({
            startDate: year,
            showDropdowns: true,
            ranges: {
                'Este año': [moment().startOf('year'), moment().endOf('year')],
                'Año pasado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            }
        }, cb);

        cb(year);
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
                    Fechas de corte semana
                </h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:nolaborables"])
                <div class="row">
                    <div class="col-md-5 col-md-offset-7">
                        <div class="row">
                            <div class="col-md-12">
                                <form action="{{ route('cortes_semana') }}">
                                    <input type="hidden" id="year" name="year">
                                    <div class="input-group">
                                        <input type="number" class="form-control" value="{{$year}}" min="1970" max="2100" name="year" id="year">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="submit">
                                                <i class="glyphicon glyphicon-search"></i>
                                            </button>
                                        </span>
                                    </div>
                                </form><br><br>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <h5>Se recuerda que el visto bueno de prenómina se debe hacer el siguiente día habil al corte.</h5>
                <table class="table table-striped table-bordered table-hover" id="tabla-data">
                    <thead>
                        <tr>
                            <th># semana</th>
                            <th>Fecha inicial</th>
                            <th>Fecha final</th>
                            <th>Año</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $data)
                        <tr>
                            <td>{{$data->num}}</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($data->ini)}}</td>
                            <td>{{\App\SUtils\SDateTimeUtils::orderDate($data->fin)}}</td>
                            <td>{{$data->year}}</td>
                            
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection