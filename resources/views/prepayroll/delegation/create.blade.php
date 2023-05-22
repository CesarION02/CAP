@extends("theme.$theme.layout")
@section('title')
    Delegación de V.º B.º de prenómina
@endsection

@section('styles1')
    <link href="{{ asset("select2js/css/select2.min.css") }}" rel="stylesheet" />
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12" id="delegationsApp">
        @include('includes.form-error')
        @include('includes.mensaje')
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Crear Delegación de V.º B.º de prenómina</h3>
                @include('layouts.usermanual', ['link' => "http://192.168.1.251/dokuwiki/doku.php?id=wiki:delegacionvobo"])
                <div class="box-tools pull-right">
                    <a href="{{ route('prepayrolldelegation.index') }}" class="btn btn-block btn-info btn-sm">
                        <i class="fa fa-fw fa-reply-all"></i> Regresar
                    </a>
                </div>
            </div>
            <form action="{{ route('prepayrolldelegation.store') }}" id="form-general" class="form-horizontal" method="POST" autocomplete="off">
                @csrf @method("post")
                <div class="box-body">
                    @include('prepayroll.delegation.form')
                </div>
                <div class="box-footer">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <button type="button" onclick="validateDelegation()" class="btn btn-success" id="guardar">Guardar</button>
                        <button type="reset" onclick="resetTheSelect()" class="btn btn-default">Deshacer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> 
@endsection

@section("scripts")
    <script src="{{ asset('select2js/js/select2.min.js') }}"></script>
    <script src="{{ asset("assets/js/axios.js") }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/vue.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/vue.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/pages/scripts/SGui.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        var oDates = <?php echo json_encode($oDates) ?>;
        var oGui = new SGui();
    </script>

    <script src="{{ asset('assets/pages/scripts/prepayroll/SDelegation.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        function validateDelegation() {
            let form = $('#form-general').serializeArray();

            if (form[4].value == form[5].value) {
                oGui.showError('El usuario ausente y el usuario encargado no pueden ser el mismo.');
                return false;
            }

            oGui.showLoading(5000);

            let payroll = form[3].value.split('_');
            axios.post("{{ route('prepayrolldelegation.validate') }}", {
                number_prepayroll: payroll[0],
                pay_way_id: form[2].value,
                year: payroll[1],
                user_delegation_id: form[4].value,
                _token: '{{ csrf_token() }}'
            })
            .then(function (response) {
                console.log(response);
                if (response.data.code == 200) {
                    swal({
                        title: "Atención",
                        text: "Esta nómina ya ha sido delegada, ¿Desea continuar?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $('#form-general').submit();
                        }
                        else {
                            swal("No se realizó ninguna acción");
                        }
                    });
                }
                else {
                    $('#form-general').submit();
                }
            })
            .catch(function (error) {
                console.log(error);
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            $('.select2-class').select2();
        });
    </script>
@endsection