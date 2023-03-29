
<div class="form-group">
    <label for="horario" class="col-lg-3 control-label">Usuarios:</label>
    <div class="col-lg-3">
        @if(isset($datas))
            <input type="text" name="usuario" id="usuario" value="{{$datas[0]->name}}" disabled>
        @else
            <select name="usuario" id="usuario" class="chosen-select" required>
                <option>Seleccione una opción</option>
                @foreach($users as $user => $index)
                
                    <option value="{{$index}}">{{$user}}</option>
                @endforeach
            </select>
        @endif
    </div>
</div>
<div class="form-group">
    <label for="horario" class="col-lg-3 control-label">Equipos biométricos:</label>
    <div class="col-lg-3">
        <select class="chosen-select" data-placeholder="Selecciones opciones" id="devices" name="devices[]" multiple>
            @foreach($devices as $device => $index)
                @if(isset($datas))
                    @foreach ($datas as $data)
                        @if($data->idDevice == $index)
                            <option selected value="{{$index}}">{{$device}}</option>
                        @else
                            <option value="{{$index}}">{{$device}}</option>   
                        @endif
                    @endforeach
                @else
                    <option value="{{$index}}">{{$device}}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>