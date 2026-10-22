
<div id="period-selection">
    {{-- Año --}}
    <div class="form-group row">
        <label class="col-lg-2 control-label requerido">Año:</label>
        <div class="col-lg-4">
            <select id="year" name="year" class="form-control">
                <option value="" disabled>Seleccione un año</option>
                @foreach ($years as $year)
                    @if($datas->year == $year)
                        <option selected value="{{ $year }}">{{ $year }}</option>
                    @else
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tipo de nómina --}}
    <div class="form-group row" style="display: none;" id="type-group">
        <label class="col-lg-2 control-label requerido">Tipo de nómina:</label>
        <div class="col-lg-4">
            <select id="type" name="type" class="form-control">
                <option value="" selected disabled>Seleccione tipo</option>
                <option value="week">Semanal</option>
                <option value="quincena">Quincenal</option>
            </select>
        </div>
    </div>

    {{-- Periodo --}}
    <div class="form-group row" id="num-group" style="display: none;">
        <label class="col-lg-2 control-label requerido" for="num">Periodo:</label>
        <div class="col-lg-4">
            <select id="num" name="num" class="form-control">
                <option value="" disabled selected>Seleccione número</option>
            </select>
        </div>
    </div>
    {{-- ¿Se desactivará? --}}
    <div class="form-group row" id="deactivate-group">
        <label class="col-lg-2 control-label requerido" for="deactivate">¿Se desactivará?</label>
        <div class="col-lg-4">
            <select id="deactivate" name="deactivate" class="form-control">
                <option value="" disabled selected>Seleccione una opción</option>
                <option value="yes" {{ old('deactivate', $datas->applies ?? '') == 1 ? 'selected' : '' }}>Sí</option>
                <option value="no" {{ old('deactivate', $datas->applies ?? '') == 0 ? 'selected' : '' }}>No</option>
            </select>
        </div>
    </div>

    {{-- Días de posposición --}}
    <div class="form-group row" id="postpone-group" style="display: none;">
        <label class="col-lg-2 control-label requerido" for="postpone_days">Días de posposición:</label>
        <div class="col-lg-4">
            <input type="number" 
                    id="postpone_days" 
                    name="postpone_days" 
                    class="form-control" 
                    min="0" 
                    placeholder="Ingrese número de días" 
                    value="{{ old('postpone_days', $datas->days ?? '0') }}">
        </div>
    </div>
    <input id="id" name="id" type="hidden" value="{{ $datas->id_payroll_closing_days }}">
</div>

<script>
    const periodsData = @json($periods);

    const isEdit = {{ isset($datas) ? 'true' : 'false' }};
    const selectedYear = "{{ $datas->year ?? '' }}";
    const selectedType = "{{ $datas->is_week ? 'week' : ($datas->is_biweek ? 'quincena' : '') }}";
    const selectedNum = "{{ $datas->week_id ?? $datas->biweek_id ?? '' }}";
    const selectedDeactivate = "{{ $datas->applies == 1 ? 'yes' : 'no' }}";
    const selectedDays = "{{ $datas->days ?? '0' }}";

    function formatearFecha(fecha) {
        const [anio, mes, dia] = fecha.split('-');
        return `${dia}/${mes}/${anio}`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const yearSelect = document.getElementById('year');
        const typeSelect = document.getElementById('type');
        const numSelect = document.getElementById('num');
        const deactivateSelect = document.getElementById('deactivate');
        const postponeDays = document.getElementById('postpone_days');

        const typeGroup = document.getElementById('type-group');
        const numGroup = document.getElementById('num-group');
        const postponeGroup = document.getElementById('postpone-group');

        // Mostrar tipo si hay año
        if (selectedYear) {
    yearSelect.value = selectedYear;
    typeGroup.style.display = 'flex';

    setTimeout(() => {
        typeSelect.value = selectedType;

        const changeEvent = new Event('change');
        typeSelect.dispatchEvent(changeEvent);

        deactivateSelect.value = selectedDeactivate;
        if (selectedDeactivate === 'no') {
            postponeGroup.style.display = 'flex';
            postponeDays.value = selectedDays;
        }
    }, 200); // aumentamos el tiempo para asegurar que todo esté renderizado
}

        // Cargar periodos si es edición
        function cargarPeriodos() {
            const year = yearSelect.value;
            const type = typeSelect.value;

            if (!year || !type || !Array.isArray(periodsData[type]?.[year])) {
                numGroup.style.display = 'none';
                return;
            }

            numSelect.innerHTML = `<option value="" disabled>Seleccione número</option>`;

            periodsData[type][year].forEach(period => {
                const option = document.createElement('option');
                option.value = period.id;

                if (type === 'week') {
                    option.textContent = `Semana ${period.num} (${formatearFecha(period.ini)} - ${formatearFecha(period.fin)})`;
                } else {
                    option.textContent = `Quincena ${period.num} (${formatearFecha(period.ini)})`;
                }

                if (String(period.id) === String(selectedNum)) {
                    option.selected = true;
                }

                numSelect.appendChild(option);
            });

            numGroup.style.display = 'flex';
        }

        yearSelect.addEventListener('change', () => {
            typeSelect.selectedIndex = 0;
            numSelect.innerHTML = `<option value="" disabled selected>Seleccione número</option>`;
            typeGroup.style.display = 'flex';
            numGroup.style.display = 'none';
        });

        typeSelect.addEventListener('change', cargarPeriodos);

        deactivateSelect.addEventListener('change', () => {
            if (deactivateSelect.value === 'no') {
                postponeGroup.style.display = 'flex';
            } else {
                postponeGroup.style.display = 'none';
                postponeDays.value = '';
            }
        });

        // 🔁 Si es edición, simulamos el flujo completo
        if (isEdit) {
            setTimeout(() => {
                typeSelect.value = selectedType;
                const changeEvent = new Event('change');
                typeSelect.dispatchEvent(changeEvent);

                deactivateSelect.value = selectedDeactivate;
                if (selectedDeactivate === 'no') {
                    postponeGroup.style.display = 'flex';
                    postponeDays.value = selectedDays;
                }
            }, 100);
        }
    });
</script>