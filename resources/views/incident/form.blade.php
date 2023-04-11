<div class="form-group">
    <label for="Tipoincidente" class="col-lg-3 control-label requerido">Tipo incidencia:</label>
    <div class="col-lg-8">
        @if(isset($datas))
            <select id="type_incidents_id" name="type_incidents_id" disabled class="form-control" onchange="tipo_incidencia(this)">
                    <option value="0">Elige una opción</option>
                @foreach($lIncidentTypes as $type => $index)
                    @if ($datas[0]->tipo == $index)
                        <option value="{{ $index }}" selected> {{$type}}</option>
                    @else
                        <option value="{{ $index }}" > {{$type}}</option>
                    @endif
                    
                @endforeach
            </select>
        @else
            <select id="type_incidents_id" name="type_incidents_id" class="form-control" onchange="tipo_incidencia(this)">
                <option value="0">Elige una opción</option>
                @foreach($lIncidentTypes as $type => $index)
                    <option value="{{ $index }}" {{old('type_incidents_id') == $index ? 'selected' : '' }}> {{$type}}</option>
                @endforeach
            </select>
        @endif   
    </div>
</div>
<div class="form-group">
    <label for="start_date" class="col-lg-3 control-label requerido">Fecha inicial:</label>
    <div class="col-lg-8">
        
        @if(isset($datas))
            <input type="date" class="form-control" name="start_date" id="start_date" value="{{$datas[0]->ini}}" class="form-control">
        @else
            <input type="date" class="form-control" name="start_date" id="start_date" value="" class="form-control">
        @endif
    </div>
</div>
<div class="form-group">
        <label for="end_date" class="col-lg-3 control-label requerido">Fecha final:</label>
        <div class="col-lg-8">
            @if(isset($datas))
                <input type="date" class="form-control" name="end_date" id="end_date" value="{{$datas[0]->fin}}" class="form-control">
            @else
                <input type="date" class="form-control" name="end_date" id="end_date" value="" class="form-control">
            @endif
        </div>
</div>
<div class="form-group">
        <label for="employee_id" class="col-lg-3 control-label requerido">Empleado:</label>
        <div class="col-lg-8">
            @if(isset($datas))
               <input type="text" class="form-control" value="{{$datas[0]->name}}"  readonly>
               <input type="hidden" class="form-control" value="{{$datas[0]->id_employee}}" id="employee_id"  name="employee_id">
            @else
                <select id="employee_id"  name="employee_id" class="form-control chosen-select">
                    @foreach($employees as $employee)
                    <option value="{{ $employee->num }}" {{old('employee_id') == $index ? 'selected' : '' }}> {{$employee->name.' - '.$employee->num_employee}}</option>
                    @endforeach
                </select>
            @endif
        </div>
</div>
<div class="form-group">
    <label for="employee_id" class="col-lg-3 control-label requerido">Día festivo correspondiente:</label>
    <div class="col-lg-8">
        @if(isset($datas))
            
        @else
            <select id="holiday_id"  name="holiday_id" class="form-control " disabled>
                @foreach($holidays as $holiday)
                    <option value="{{ $holiday->id }}"> {{$holiday->name.' - '.$holiday->fecha}}</option>
                @endforeach
            </select>
        @endif
    </div>
</div>
<div class="form-gruop">
    <label class="col-lg-3 control-label" for="">Comentarios frecuentes:</label>
    <div class="col-lg-8">
        @if(isset($datas))
            @if($activar == 1)
                <select class="form-control select2-class" id="comentFrec" style="width: 80%;" title="Lista de comentarios frecuentes.">
                    @foreach ($lFrecuentComments as $comment)
                        <option ="comment in lComments">{{$comment->comment}}</option>   
                    @endforeach  
                </select>
            @else
                <select disabled class="form-control select2-class" id="comentFrec" style="width: 80%;" title="Lista de comentarios frecuentes.">
                    @foreach ($lFrecuentComments as $comment)
                        <option ="comment in lComments">{{$comment->comment}}</option>   
                    @endforeach  
                </select>
            @endif
        
        @else
            <select disabled class="form-control select2-class" id="comentFrec" style="width: 80%;" title="Lista de comentarios frecuentes.">
                @foreach ($lFrecuentComments as $comment)
                    <option ="comment in lComments">{{$comment->comment}}</option>   
                @endforeach  
            </select>
        @endif
        <button class="btn btn-success" type="button" title="Agregar texto." style="border-radius: 50%; padding: 3px 6px; font-size: 10px;" onclick="addComment()"><span class="glyphicon glyphicon-arrow-right"></span></button>
        <small class="text-muted">Debe dar click en el botón <span class="glyphicon glyphicon-arrow-right"></span> para agregar comentario</small>
    </div>
</div>
  <br>
<div class="form-gruop">
    <label class="col-lg-3 control-label requerido" for="">Comentarios:</label>
    <div class="col-lg-8">
      
        @if(isset($datas))
            @if($activar == 1)
                @if($hay_ajustes == 0)
                    <textarea required id="comentarios" name="comentarios" title="Escribe el comentario que deseas que aparezca en el renglón."  class="form-control" style="resize: none; width: 350px; height: 115px;"></textarea>
                @else
                <textarea required id="comentarios" name="comentarios" title="Escribe el comentario que deseas que aparezca en el renglón."  class="form-control" style="resize: none; width: 350px; height: 115px;">{{$comment_adjust[0]->comments}}</textarea>
                @endif
            @else
                <textarea id="comentarios" name="comentarios" title="Escribe el comentario que deseas que aparezca en el renglón."  class="form-control" style="resize: none; width: 350px; height: 115px;" disabled></textarea>
            @endif
        @else
            <textarea required id="comentarios" name="comentarios" title="Escribe el comentario que deseas que aparezca en el renglón."  class="form-control" style="resize: none; width: 350px; height: 115px;" disabled></textarea>
        @endif
    </div>
</div>
<input type="hidden" id="sincomentarios" name="sincomentarios" value="0">
@if(isset($is_medical))
    <input type="hidden" id="is_medical" name="is_medical" value="{{$is_medical}}">
@else
    <input type="hidden" id="is_medical" name="is_medical" value="0" > 
@endif