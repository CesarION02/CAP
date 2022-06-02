
<div class="form-group">
    <label for="horario" class="col-lg-3 control-label">Usuarios:</label>
    <div class="col-lg-3">
        @if(isset($empleados))
            <input type="text" name="usuario" id="usuario" value="{{$empleados[0]->nombre}}" disabled>
        @else
            <select name="usuario" id="usuario">
                @foreach($users as $user => $index)
                
                    <option value="{{$index}}">{{$user}}</option>
                @endforeach
            </select>
        @endif
    </div>
</div>
<div class="form-group">
    <label for="horario" class="col-lg-3 control-label">Grupos departamentos CAP:</label>
    <div class="col-lg-3">
        <?php $comparacion = 0; ?>
        <select class="chosen-select" id="dgu" name="dgu[]" multiple>
            @foreach($departments as $department => $index)
                @if(isset($datas))
                    @for($i = 0 ; count($datas) > $i ; $i++)
                        @if($datas[$i]->dg == $index)
                            <option selected value="{{$index}}">{{$department}}</option>
                            <?php $comparacion = 1;?>
                        @endif 
                    @endfor
                    @if($comparacion == 0)
                        <option value="{{$index}}">{{$department}}</option>
                    @endif
                    <?php $comparacion = 0;?>
                @else
                    <option value="{{$index}}">{{$department}}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>