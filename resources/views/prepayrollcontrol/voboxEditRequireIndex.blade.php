@extends("theme.$theme.layout")
@section('title')
    VoBo de prenóminas
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{asset("daterangepicker/daterangepicker.css")}}">
@endsection

@section("scripts")
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/admin/datatable/index.js")}}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("dt/datatables.js") }}" type="text/javascript"></script>
    <script src="{{ asset('dt/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset("assets/js/moment/moment.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/moment-with-locales.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/moment/datetime-moment.js") }}" type="text/javascript"></script>
	<script src="{{ asset('dt/buttons.flash.min.js') }}"></script>
	<script src="{{ asset('dt/jszip.min.js') }}"></script>
	<script src="{{ asset('dt/pdfmake.min.js') }}"></script>
	<script src="{{ asset('dt/vfs_fonts.js') }}"></script>
	<script src="{{ asset('dt/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('dt/buttons.print.min.js') }}"></script>
    <script src="{{ asset("daterangepicker/daterangepicker.js") }}" type="text/javascript"></script>
    <script src="{{asset("assets/pages/scripts/SGui.js")}}" type="text/javascript"></script>
    <script type="text/javascript">
        function GlobalData () {
            this.oData = <?php echo json_encode($lControls) ?>;
        }
        var oServerData = new GlobalData();
    </script>
    <script>
        var app = new Vue({
            el: '#EditRequireVobo',
            data: {
                oData: oServerData.oData,
                user_vobo: null,
                user_id: null,
                idControl: null,
                Require: null,
                FechaIni: null,
                FechaFin: null,
                index: null
            },
            mounted(){
                console.log(this.oData);
            },
            methods: {
                setRequire(index, user, isRequire, ini, fin, idControl, idUser){
                    this.user_vobo = user;
                    this.user_id = idUser;
                    this.FechaIni = ini;
                    this.FechaFin = fin;
                    this.Require = isRequire;
                    this.index = index;
                    this.idControl = idControl;
                },
                saveRequire() {
                    $.ajax({
                        type:'POST',
                        url: '{{$routeSave}}',
                        data:{ idControl: this.idControl, require: this.Require, user_id: this.user_id, _token: '{{csrf_token()}}' },
                        success: data => {
                            this.oData[this.index].is_required = data.value;
                            swal("Registro actualizado correctamente");
                        },
                        error: data => {
                            swal("Error al actualizar el registro");
                        }
                    });
                }
            }
        });
    </script>
<script>
    $(document).ready( function () {
        var select = document.getElementById("pay-type");
        var value = '<?php echo $idPreNomina; ?>';
        
        var columns = [];
        if(value == "week"){
            select.value = 1;
            columns = [1];
        }else if(value == "biweek"){
            select.value = 2;
            columns = [0];
        }


        $.fn.dataTable.moment('DD/MM/YYYY');

        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                // var min = parseInt( $('#min').val(), 10 );
                let payTypeVal = parseInt( $('#pay-type').val(), 10 );
                let sem = 0;
                let qui = 0;

                switch (payTypeVal) {
                    case 3:
                        return true;

                    case 1:
                        sem = parseInt( data[0] );
                        return sem > 0;

                    case 2:
                        qui = parseInt( data[1] );
                        return qui > 0;

                    default:
                        break;
                }

                return false;
            }
        );

        var vobosTable = $('#myTable').DataTable({
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
            "columnDefs": [
                {
                    "targets": columns,
                    "visible": false,
                    "searchable": true
                }
            ],
            "order": [[ 2, 'asc' ]],
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
            vobosTable.draw();
        });
    });


</script>
<script>
    
</script>
@endsection

@section('content')
<div class="row" id="EditRequireVobo">
<!-- Modal -->
<div id="requireEditModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@{{user_vobo}}</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="requerido" class="form-label">@{{FechaIni}} - @{{FechaFin}}</label>
                        <select class="form-select" name="requerido" v-model="Require">
                            <option :selected="Require == 1" value="1">Requerido</option>
                            <option :selected="Require == 0" value="0">No requerido</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" v-on:click="saveRequire()">Aceptar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
    <div class="col-lg-12">
        @include('includes.mensaje')
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Editar Prenóminas VoBos</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.233:8080/dokuwiki/doku.php"])
                <div class="row">
                    <div class="col-md-8 col-md-offset-4">
                        <div class="row">
                            <div class="col-md-7 col-md-offset-1">
                            <form action="{{ route('vobos_edit_require', ['id' => $idPreNomina])}}">
                                <div class="input-group">
                                    @include('controls.calendar', ['start_date' => $start_date, 'end_date' => $end_date,
                                                        'start_date_name' => 'start_date', 'end_date_name' => 'end_date']) 
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit">
                                            <i class="glyphicon glyphicon-search"></i>
                                        </button>
                                    </span>
                                </div>
                            </form>
                            </div>
                            <div class="col-md-3 col-md-offset-1" hidden>
                                <select name="pay-type" id="pay-type" class="form-control">
                                    <option value="1">Semana</option>
                                    <option value="2">Quincena</option>
                                    <option value="3">Todos</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-condensed" style="width:100%" id="myTable">
                    <thead>
                        <tr>
                            <th title="Número de semana"># Sem</th>
                            <th title="Número de quincena"># Qui</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th title="Usuario visto bueno">Usr VoBo</th>
                            <th title="Requerido">Req</th>
                            <th title="Visto bueno">VoBo</th>
                            <th>-</th>
                        </tr>
                    </thead>
                    <tbody>
                            <tr v-for="(data, index) in oData">
                                <td>@{{ data.num_week }}</td>
                                <td>@{{ data.num_biweek }}</td>
                                <td>@{{ data.ini  }}</td>
                                <td>@{{ data.fin }}</td>
                                <td>@{{ data.name }}</td>
                                <td>@{{ data.is_required ? "SÍ" : "NO" }}</td>
                                <td>@{{ data.is_vobo ? "SÍ" : "NO" }}</td>
                                <td>
                                    <a href="#" data-toggle="modal" data-target="#requireEditModal"
                                        v-on:click="setRequire(index, data.name, data.is_required, data.ini, data.fin, data.id_control, data.user_vobo_id)">
                                        <span class="fa fa-gear"></span>
                                    </a>
                                </td>
                            </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection