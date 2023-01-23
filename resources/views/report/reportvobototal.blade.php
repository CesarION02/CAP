@if ($isPrepayrollInspection && isset($oPrepayrollCtrl) && !is_null($oPrepayrollCtrl))
    <div>
        <br>
        <div class="row no-gutters" style="border: 1px solid rgb(45, 151, 250); border-radius: 16px;">
            <div class="col-md-6">
                <p>V.º B.º periodo: <b>{{ $sStartDate }}</b> - <b>{{ $sEndDate }}</b>. P. pago: <b>{{ $sPayWay }}</b>.</p>
            </div>
            <div class="col-md-2">                                                    
                @if (! $oPrepayrollCtrl->is_vobo)
                    <form id="form_vobo" action="{{ route('dar_vobo', [$oPrepayrollCtrl->id_control, $sPayWay == \SCons::PAY_W_S ? "week" : "biweek"]) }}" method="POST">
                        @csrf
                        Autorizar
                        <input type="hidden" id="can_skip_id" name="can_skip" value="0">
                        <?php
                            $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";  
                            $CurPageURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 
                        ?>
                        <input type="hidden" name="back_url" value="{{ $CurPageURL }}">
                        <input type="hidden" name="id_delegation" value="{{ $idDelegation }}" >
                        <button style="background-color: rgb(37, 255, 135)" title="Visto bueno" type="submit" id="btnSubmit"><i class="fa fa-check" aria-hidden="true"></i></button>
                    </form>
                @else
                    <div style="text-align: center; background-color: rgb(37, 255, 135);">
                        <b>Autorizado</b>
                    </div>
                @endif
            </div>
            @if ($oPrepayrollCtrl->is_vobo || $oPrepayrollCtrl->is_rejected)
                <div class="col-md-2">
                    {{ $oPrepayrollCtrl->username }}
                </div>
            @endif
            <div class="col-md-2">
                @if ($oPrepayrollCtrl->is_rejected)
                    <div style="background-color: rgb(247, 76, 76); border-radius: 12px; text-align: center;">
                        {{ "Rechazado: ".$oPrepayrollCtrl->dt_rejected }}
                    </div>
                @else
                    <form id="form_vobo" action="{{ route('rechazar_vobo', [$oPrepayrollCtrl->id_control, $sPayWay == \SCons::PAY_W_S ? "week" : "biweek"]) }}" method="POST">
                        @csrf
                        Rechazar
                        <input type="hidden" id="can_skip_id" name="can_skip" value="0">
                        <?php
                            $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";  
                            $CurPageURL = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 
                        ?>
                        <input type="hidden" name="back_url" value="{{ $CurPageURL }}">
                        <input type="hidden" name="id_delegation" value="{{ $idDelegation }}" >
                        <button style="background-color: rgb(247, 76, 76)" title="Visto bueno" type="submit" id="btnSubmit">
                            <i class="fa fa-ban" aria-hidden="true"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    <br>
@endif