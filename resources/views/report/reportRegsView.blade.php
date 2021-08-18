@extends("theme.$theme.layout")
@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/datatables.css") }}">
@endsection
@section('title')
Reporte Entradas/Salidas
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border row">
                <div class="col-md-10">
                    <h3 class="box-title">Reporte entrada/salida 2</h3>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="sel-collaborator" id="sel-collaborator">
                        <option value="0" selected>Todos</option>
                        <option value="1">Empleados</option>
                        <option value="2">Becarios</option>
                    </select>
                </div>
                </div>
            </div>
            <div class="box-body" id="reportApp">
                <div class="row">
                    <div class="col-md-12">
                        <table id="myTable" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th># Empleado</th>
                                    <th>Empleado</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Tipo checada</th>
                                    <th>external_id</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="registry in oData.lRegistries">
                                    <td>@{{ registry.num_employee }}</td>
                                    <td>@{{ registry.name }}</td>
                                    <td>@{{ vueGui.formatDate(registry.date) }}</td>
                                    <td>@{{ registry.time }}</td>
                                    <td>@{{ registry.type_id == 1 ? "ENTRADA" : "SALIDA" }}</td>
                                    <td>@{{ registry.external_id }}</td>
                                </tr>
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
    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
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
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    
    <script>
        function GlobalData () {
            this.reportType = <?php echo json_encode($reportType) ?>;
            this.lRegistries = <?php echo json_encode($lRegistries) ?>;
        }
        
        var oGui = new SGui();
        var oData = new GlobalData();
    </script>
    <script src="{{asset("assets/pages/scripts/report/regisView.js")}}" type="text/javascript"></script>

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

    <script>
        $(document).ready( function () {
            $.fn.dataTable.moment('DD/MM/YYYY');

            $.fn.dataTable.ext.search.push(
                function( settings, data, dataIndex ) {
                    // var min = parseInt( $('#min').val(), 10 );
                    let collaboratorVal = parseInt( $('#sel-collaborator').val(), 10 );
                    let externalId = 0;

                    switch (collaboratorVal) {
                        case 0:
                            return true;

                        case 1:
                            externalId = parseInt( data[5] );
                            return externalId > 0;

                        case 2:
                            externalId = parseInt( data[5] );
                            return ! (externalId > 0);

                        default:
                            break;
                    }

                    return false;
                }
            );

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
                "colReorder": true,
                columnDefs: [
                    {
                        targets: [ 5 ],
                        visible: false
                    }
                ],
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

            $('#sel-collaborator').change( function() {
                oTable.draw();
            });
        });
    </script>
@endsection