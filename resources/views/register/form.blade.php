<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido">Empleado:</label>
    <div class="col-lg-8">
        
            @if(isset($datas))
                <select data-placeholder="Selecciona opciones..." class="chosen-select" name="employee_id" id="employee_id">
                    @for($i = 0 ; count($employees) > $i ; $i++)
                        @if($datas->employee_id == $employees[$i]->id)
                            <option selected value="{{$employees[$i]->id}}">{{$employees[$i]->nameEmployee}} - {{$employees[$i]->numEmployee}}</option>
                        @else
                            <option value="{{$employees[$i]->id}}">{{$employees[$i]->nameEmployee}} - {{$employees[$i]->numEmployee}}</option>
                        @endif
                    @endfor
            @else
                <select id="selEmployee" v-model="employee" data-placeholder="Selecciona opciones..." class="chosen-select" name="employee_id" id="employee_id">
                    <option value="0">Seleccione empleado</option>
                    @for($i = 0 ; count($employees) > $i ; $i++)
                        <option value="{{$employees[$i]->id}}">{{$employees[$i]->nameEmployee}} - {{$employees[$i]->numEmployee}}</option>
                    @endfor
            @endif
        </select>
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Fecha:</label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            <input type="date" name="date" id="date" value="{{$datas->date}}" readonly>
        @else
            <input v-model="date" type="date" name="date" id="date">
        @endif
        
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label"></label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            
        @else
            <button type="button" class="btn btn-primary" v-on:click="getChecks('{{route('registro_get_registry')}}');">Ver checadas</button>
        @endif
        
    </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label"></label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            
        @else
            <table v-if="lRegistries.length > 0 && canCheck" class="display table table-striped table-bordered table-hover" style="width:70%">
                <thead>
                    <tr>
                        <th>fecha</th>
                        <th>Hora</th>
                        <th>Tipo</th>
                        <th>-</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="registry in lRegistries">
                        <td>@{{registry.date}}</td>
                        <td>@{{registry.time}}</td>
                        <td>@{{registry.type_id == 1 ? 'ENTRADA' : ( registry.type_id == 2 ? 'SALIDA' : '')}}</td>
                        <td>@{{registry.form_creation_id == 2 ? 'Registro manual' : ( registry.form_creation_id == 4 ? 'Registro checador' : '')}}</td>
                    </tr>
                </tbody>
            </table>
            <p v-if="canCheck" style="color: red;">@{{messageChecks}}</p>
            <p v-if="lIncidents.length > 0 && canCheck" style="color: red;">El colaborador tiene una incidencia para el dia @{{date}}</p>
        @endif
    </div>
</div>
<div class="form-group">
    <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Hora:</label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            <input type="time" name="time" id="time" value="{{$datas->time}}" required>
        @else
            <input :disabled="!canCheck" v-model="time" type="time" name="time" id="time">
        @endif
        
    </div>
</div>
@if(! isset($datas))
    <div class="form-group">
        <label for="nombre" class="col-lg-3 control-label requerido" for="optradio">Tipo checada:</label>
        <div class="row">
            <div class="col-md-2">
                <label><input :disabled="!canCheck" v-model="picked" v-on:change="onTypeChange()" type="radio" name="optradio" id="optradio" value="single">Sencilla</label>
            </div>
            <div class="col-md-2">
                <label><input :disabled="!canCheck" v-model="picked" v-on:change="onTypeChange()" type="radio" name="optradio" id="optradio" value="cut">Corte</label>
            </div>
        </div>
    </div>
@endif
<div class="form-group" v-if="isSingle">
    <label for="nombre" class="col-lg-3 control-label requerido" for="start_date">Tipo checada:</label>
    <div class="col-lg-8">

        @if(isset($datas))
            @if($datas->type_id == 1)
                <select name="type_id" id="type_id">
                    <option selected value="1">Entrada</option>
                    <option value="2">Salida</option>
                </select>
            @else
                <select name="type_id" id="type_id">
                    <option value="0">Seleccione tipo</option>
                    <option value="1">Entrada</option>
                    <option selected value="2">Salida</option>
                </select>
            @endif
        @else
            <select :disabled="!canCheck" v-model="type" name="type_id" id="type_id">
                <option value="0">Seleccione tipo</option>
                <option value="1">Entrada</option>
                <option value="2">Salida</option>
            </select>
        @endif
        
    </div>
</div>
