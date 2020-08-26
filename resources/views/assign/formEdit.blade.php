<div class="form-group">
    <label for="start_date" class="col-lg-3 control-label">Fecha inicial:</label>
    <div class="col-lg-3">
        @if(isset($assigns))
            <input type="date" name="start_date" id="start_date" value="{{$assigns[0]->startDate}}" >
        @else
        <input type="date" name="start_date" id="start_date">
        @endif
    </div>  
    <label for="departamento" class="col-lg-2 control-label">Fecha final:</label>
    <div class="col-lg-3">
        @if(isset($assigns))
            <input type="date" name="end_date" id="end_date" value="{{$assigns[0]->endDate}}" >
        @else
            <input type="date" name="end_date" id="end_date">
        @endif
    </div> 
</div> 
<?php $auxContador = 0?>
@for($i = 0 ; count($assigns) > $i ; $i++ )
    <?php $auxContador++ ;
        $orden = "orden".$auxContador;
        $orden = "orden".$auxContador
    ?>
    <div class="form-group">
        <label for="plantilla" class="col-lg-3 control-label">Plantilla:</label>
        <div class="col-lg-3">    
            <input type="text" value="{{$assigns[$i]->templateName}}" disabled>
        </div>
        <label for="orden" class="col-lg-1 control-label">Orden:</label>
        <div class="col-md-1">
            <input type="number" name="{{$orden}}" id="{{$orden}}" value="{{$assigns[$i]->orden}}" style="width:70%">
        </div>
    </div>
    @for($j = 0 ; 7 > $j ; $j++ )
        <?php if($j != 0){$i++;}?>
        <div class="form-group">
            <label for="plantilla" class="col-lg-3 control-label">{{$assigns[$i]->dayName}}</label>
            <div class="col-lg-2">    
                <input type="text" value="{{$assigns[$i]->entry}}" disabled style="width:70%">
            </div>
            <div class="col-lg-2">    
                <input type="text" value="{{$assigns[$i]->departure}}" disabled style="width:70%">
            </div>    
        </div>
        
    @endfor
@endfor
