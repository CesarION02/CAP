@extends("theme.$theme.layout")
@section('title')
    Grupos de prenómina
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
    <style>
        .th {
            font-size: 10pt;
        }
        .tr {
            font-size: 5pt;
        }
    </style>
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.print.min.js') }}"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    {{-- <script src="{{ asset("assets/pages/scripts/prepayroll/SAdjustsAuth.js") }}" type="text/javascript"></script> --}}
<script>
    $(document).ready( function () {
        $.fn.dataTable.moment('DD/MM/YYYY');
        $('#adjusts_table').DataTable({
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
        });

    });


</script>
<script>
    moment.locale('es');
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Grupos de prenómina</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php?id=wiki:grupoprenempleados"])
                <div class="row">
                    <div class="col-md-5 col-md-offset-7">
                        <div class="row">
                            <div style="text-align: right" class="col-md-12">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body" id="adjustsApp">
                <table class="table table-striped table-bordered table-hover" id="adjusts_table">
                    <thead>
                        <tr 
                            {{-- style="font-size: 10pt;" --}}
                        >
                            <th>Num</th>
                            <th>Empleado</th>
                            <th>Grupo prenómina</th>
                            <th>Usr encargado</th>
                            <th>Seleccione nuevo grupo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lEmployees as $emp)
                            <tr 
                                {{-- style="font-size: 8pt;" --}}
                            >
                                <td>{{ str_pad($emp->num_employee, 6, "0", STR_PAD_LEFT) }}</td>
                                <td>{{ $emp->name }}</td>
                                <td>{{ $emp->group_name }}</td>
                                <td>{{ $emp->gr_titular }}</td>
                                <td>
                                    <form action="{{ route('cambiar_grupo') }}" method="post">
                                        @csrf
                                        <div style="white-space: nowrap;">
                                            <select name="new_group" class="form-select form-select-sm">
                                                <option value="0" {{ $emp->id_group == null ? "selected" : "" }}>NINGUNO</option>
                                                @foreach ($groups as $group)
                                                    <option value="{{ $group->id_group }}" {{ $group->id_group == $emp->id_group ? "selected" : "" }}>
                                                        {{ $group->group_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input value="{{ $emp->id }}" type="hidden" name="emp_id">
                                            <button class="btn btn-success btn-sm" type="submit">
                                                <span><i class="fa fa-check-circle" aria-hidden="true"></i></span>
                                            </button>
                                        </div>
                                    </form>
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