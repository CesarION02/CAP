<div class="form-group">
    <label for="employee_id" class="col-lg-3 control-label requerido">Empleado:</label>
    <div class="col-lg-8">
        @if(isset($datas))
           <input type="text" class="form-control" value="{{ $datas->name . " - " . $datas->num_employee }}"  readonly>
           <input type="hidden" class="form-control" value="{{ $datas->id_employee }}" id="employee_id"  name="employee_id">
        @else
            <select id="employee_id"  name="employee_id" class="form-control">
                @foreach($employees as $employee)
                    <option value="{{ $employee->num }}">{{ $employee->name.' - '.$employee->num_employee }}</option>
                @endforeach
            </select>
        @endif
    </div>
</div>
<div class="form-group">
    <label for="Tipoincidente" class="col-lg-3 control-label requerido">Tipo incidencia:</label>
    <div class="col-lg-8">
        @if(isset($datas))
            <select id="type_incidents_id" name="type_incidents_id" disabled class="form-control">
                    <option value="0">Elige una opción</option>
                @foreach($lIncidentTypes as $type => $index)
                    @if ($datas->tipo == $index)
                        <option value="{{ $index }}" selected>{{ $type }}</option>
                    @else
                        <option value="{{ $index }}"> {{$type}}</option>
                    @endif
                @endforeach
            </select>
        @else
            <select id="type_incidents_id" name="type_incidents_id" class="form-control" v-on:change="onChangeIncidentType($event)" required>
                <option value="0">Elige una opción</option>
                @foreach($lIncidentTypes as $type => $index)
                    <option value="{{ $index }}" {{ old('type_incidents_id') == $index ? 'selected' : '' }}> {{ $type }} </option>
                @endforeach
            </select>
        @endif   
    </div>
</div>
<div v-if="showHoliday" class="form-group">
    <label for="employee_id" class="col-lg-3 control-label requerido">Festivo correspondiente:</label>
    <div class="col-lg-8">
        <select id="holiday_id" name="holiday_id" class="form-control ">
            @foreach($holidays as $holiday)
                <option value="{{ $holiday->id }}" {{ isset($iIdHoliday) && $iIdHoliday == $holiday->id ? "selected" : "" }}>{{ \Carbon\Carbon::parse($holiday->fecha)->format('d/m/Y') . ' - ' . $holiday->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label for="start_date" class="col-lg-3 control-label requerido">Fecha inicial:</label>
    <div class="col-lg-8">
        @if(isset($datas))
            <input type="date" class="form-control" name="start_date" id="start_date" value="{{ $datas->ini }}" class="form-control" required>
        @else
            <input type="date" class="form-control" name="start_date" id="start_date" value="" class="form-control" required>
        @endif
    </div>
</div>
<div class="form-group">
        <label for="end_date" class="col-lg-3 control-label requerido">Fecha final:</label>
        <div class="col-lg-8">
            @if(isset($datas))
                <input type="date" class="form-control" name="end_date" id="end_date" value="{{$datas->fin}}" class="form-control" required>
            @else
                <input type="date" class="form-control" name="end_date" id="end_date" value="" class="form-control" required>
            @endif
        </div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label" for="">Comentarios frecuentes:</label>
    <div class="col-lg-6">
        <select class="form-control" id="comentFrec" style="width: 100%;" title="Lista de comentarios frecuentes.">
            @foreach ($lFrecuentComments as $comment)
                <option value="{{ $comment->comment }}">{{ $comment->comment }}</option>
            @endforeach  
        </select>
        <small class="text-muted">Debe dar click en el botón <span class="glyphicon glyphicon-arrow-right"></span> para agregar comentario</small>
    </div>
    <div class="col-lg-1">
        <button v-on:click="addComment()" class="btn btn-success" type="button" title="Agregar texto." style="border-radius: 50%; padding: 3px 6px; font-size: 10px;">
            <span class="glyphicon glyphicon-arrow-right"></span>
        </button>
    </div>
</div>
<br>
<div class="form-group">
    <label :class="'col-lg-3 control-label ' + (commentRequired ? 'requerido' : '')" for="">Comentarios:</label>
    <div class="col-lg-8">
        <textarea :required="commentRequired" 
                    id="comentarios" 
                    name="comentarios" 
                    title="Escribe el comentario que deseas que aparezca en el renglón." 
                    class="form-control" style="resize: none; width: 350px; height: 115px;">{{ isset($oAdjust) ? $oAdjust->comments : "" }}</textarea>
    </div>
</div>