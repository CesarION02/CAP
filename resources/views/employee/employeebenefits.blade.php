@extends("theme.$theme.layoutcustom")

@section('styles1')
    <link rel="stylesheet" href="{{ asset("dt/nv/datatables.css") }}">
    <link rel="stylesheet" href="{{ asset("assets/css/reportD.css") }}">
    <style>
        tr {
            font-size: 100%;
        }
    </style>
@endsection

@section('title')
    {{ $sTitle }}
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $sTitle }}</h3>
                <div class="box-tools pull-right">
                </div>
            </div>
            <div class="box-body" id="divEmployeeBenefits">
                <div class="row">
                    <div class="col-md-12">
                        <table id="delays_table" class="table table-condensed" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="employee in oData.lEmployees">
                                    <td>@{{ vueGui.pad(employee.num_employee, 6) }}</td>
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
    <script src="{{ asset("assets/pages/scripts/SGui.js") }}" type="text/javascript"></script>
    
    <script>
        var oGui = new SGui();
        oGui.showLoading(5000);
    </script>

    <script src="{{ asset("assets/js/chosen.jquery.min.js") }}" type="text/javascript"></script>
    <script src="{{ asset("assets/js/vue.js") }}" type="text/javascript"></script>
    <script src="{{ asset("dt/nv/datatables.js") }}" type="text/javascript"></script>
    <script>
        function GlobalData () {
            this.lEmployees = <?php echo json_encode($lEmployees) ?>;
        }
        
        var oData = new GlobalData();
        
    </script>

    <script src="{{ asset("assets/js/vues/SVueEmployeeBenefits.js") }}" type="text/javascript"></script>

@endsection