<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Nombre grupo:</label>
    <div class="col-lg-8">
    <input type="text" name="name" id="name" class="form-control" required value="<?php if(isset($group)){ echo $group[0]->name;}else{echo " ";} ?>" />
    </div>
</div>
<div class="form-group">
    <?php $contador = 0; ?>
    @foreach($workshift as $workshift)
        <?php $contador++; 
              $seleccion = 0;
        ?>
        <div class="col-md-4">
            @if(isset($group))
                @foreach($group as $groups)
                    @if($groups->workshift == $workshift->id)
                        <input type="checkbox" checked name="check{{$contador}}" id="check{{$contador}}" value="{{$workshift->id}}"/>{{$workshift->name.' '.$workshift->entry.'-'.$workshift->departure}}
                        <?php $seleccion = 1; ?>
                    @endif
                @endforeach
                @if($seleccion != 1)
                    <input type="checkbox" name="check{{$contador}}" id="check{{$contador}}" value="{{$workshift->id}}"/>{{$workshift->name.' '.$workshift->entry.'-'.$workshift->departure}} 
                @endif
            @else
                <input type="checkbox" name="check{{$contador}}" id="check{{$contador}}" value="{{$workshift->id}}"/>{{$workshift->name.' '.$workshift->entry.'-'.$workshift->departure}}
            @endif
        </div>  
    @endforeach
    <input type="hidden" name="contador" id="contador" value="{{$contador}}">
</div>