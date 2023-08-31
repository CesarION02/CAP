@extends("theme.$theme.layoutcustom")
@section('title')
Mapa de prenómina
@endsection

@section('styles1')
    <link rel="stylesheet" href="{{ asset("pickleTree/pickletree.css") }}">
@endsection

@section('content')
    <div class="row" id="prepayrollApp">
        <div class="col-lg-12">
            @include('includes.mensaje')
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Mapa de prenómina</h3>
                    @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php"])
                    <br>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="">GRUPO DE PRENÓMINA (Encargado(s))</label>
                        </div>
                        <div class="col-md-2 col-md-offset-2">
                            <a href="{{ route('gr_emps_index') }}" class="btn btn-info btn-sm" title="Muestra todos los empleados y su grupo de prenómina">
                                <i class="fa fa-users"></i> Empleados vs grupos
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('create_prepayroll_group') }}" title="Crear nuevo grupo de prenómina" class="btn btn-success btn-sm" id="btn_create">
                                <i class="fa fa-plus-circle"></i> Nuevo grupo
                            </a>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-10">
                            @foreach ($lFathersGroups as $fGroup)
                                <div style="font-size: 10px; margin-bottom: 5px;" id="{{ "div_" . $fGroup->id_group }}" class="tree"></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('prepayroll.groups.usersModal')
        @include('prepayroll.groups.prepayrollModal')
    </div>

@endsection

@section("scripts")
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    <script>
        function GlobalData () {
            this.lFathersGroups = <?php echo json_encode($lFathersGroups) ?>;
            this.getCfgsRoute = <?php echo json_encode($getCfgsRoute) ?>;
            this.saveCfgsRoute = <?php echo json_encode($saveCfgsRoute) ?>;
        }

        var oData = new GlobalData();
    </script>
    <script src="{{ asset("pickleTree/pickletree.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/pages/scripts/prepayroll/SPrepayrollGroups.js") }}" type="text/javascript"></script>
@endsection