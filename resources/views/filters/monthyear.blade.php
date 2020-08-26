<div class="input-group">
    <input type="text" value="{{ $monthYear }}" size="7" id="yearmonth-picker"
            name="month_year"
            class="form-control form-control-sm"
            placeholder="08/2020"
            aria-label="mes/año"
            aria-describedby="basic-addon2">

    <input type="hidden" name="filter_type" id="filter-type">

    <div class="input-group-btn">
        <button onclick="setFilterType(1)" 
            id="monthBtn"
            class="btn btn-default" 
            type="submit">Mes</button>
        <button onclick="setFilterType(2)" 
            id="yearBtn"
            class="btn btn-default" 
            type="submit">Año</button>
    </div>
</div>