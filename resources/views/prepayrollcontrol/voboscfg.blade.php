@extends("theme.$theme.layout")
@section('title')
    Configuración VoBo de prenóminas
@endsection

@section("scripts")
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
<script>
    $(document).ready( function () {
        $.fn.dataTable.moment('DD/MM/YYYY');
        var oTable = $('#myTable').DataTable({
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
            columnDefs: [
                    {
                        targets: [ 0 ],
                        visible: false
                    }
            ],
            "order": [[ 1, 'asc' ]],
            "scrollX": true,
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

        $('#pay-type').change( function() {
            oTable.draw();
        });

        moment.locale('es');

        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                // var min = parseInt( $('#min').val(), 10 );
                let payTypeVal = parseInt( $('#pay-type').val(), 10 );

                if (payTypeVal == 0) {
                    return true;
                }

                let rowPayType = parseInt( data[0], 10 );
                return payTypeVal == rowPayType;
            }
        );
    });
    
</script>
    
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Configuración VoBo de prenóminas</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:configvobo"])
                <div class="row">
                    <div class="col-md-8 col-md-offset-4">
                        <div class="row">
                            <div class="col-md-3 col-md-offset-7">
                                <select name="pay-type" id="pay-type" class="form-control">
                                    <option value="1">Semana</option>
                                    <option value="2">Quincena</option>
                                    <option value="0"selected>Todos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('cfg_vobos_create') }}" class="btn btn-success">Nuevo</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-condensed" style="width:100%" id="myTable">
                    <thead>
                        <tr>
                            <th>payType</th>
                            <th title="Número de semana">Tipo</th>
                            <th title="Orden de jerarquía">Orden</th>
                            <th title="Es requerido">Requerido</th>
                            <th title="Es global">Global</th>
                            <th title="Rol">Rol</th>
                            <th title="Nombre de usuario">Usuario</th>
                            <th>-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($configurations as $oCfg)
                            <tr>
                                <td>{{ $oCfg->is_week ? 1 : 2 }}</td>
                                <td>{{ $oCfg->is_week ? "SEMANA" : "QUINCENA" }}</td>
                                <td>{{ $oCfg->order_vobo }}</td>
                                <td>{{ $oCfg->is_required ? "SÍ" : "NO" }}</td>
                                <td>{{ $oCfg->is_global ? "SÍ" : "NO" }}</td>
                                <td>{{ $oCfg->rol_n_name }}</td>
                                <td>{{ $oCfg->name }}</td>
                                <td>
                                    <a href="{{ route('edit_cfg', $oCfg->id_configuration) }}"><i class="fa fa-pencil" aria-hidden="true"></i></a>
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