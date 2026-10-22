
<div id="period-selection">
    <div class="form-group row">
        <label class="col-lg-2 control-label requerido">Año:</label>
        <div class="col-lg-4">
            <select id="year" class="form-control">
                <option value="" selected disabled>Seleccione un año</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>

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

    <div class="form-group row" id="num-group" style="display: none;">
        <label class="col-lg-2 control-label requerido" for="num">Periodo:</label>
        <div class="col-lg-4">
            <select id="num" name="num" class="form-control">
                <option value="" disabled selected>Seleccione número</option>
            </select>
        </div>
    </div>
    <div class="form-group row" id="deactivate-group">
        <label class="col-lg-2 control-label requerido" for="deactivate">¿Se desactivará?</label>
        <div class="col-lg-4">
            <select id="deactivate" name="deactivate" class="form-control">
                <option value="" disabled selected>Seleccione una opción</option>
                <option value="yes">Sí</option>
                <option value="no">No</option>
            </select>
        </div>
    </div>

    <div class="form-group row" id="postpone-group" style="display: none;">
        <label class="col-lg-2 control-label requerido" for="postpone_days">Días de posposición:</label>
        <div class="col-lg-4">
            <input type="number" id="postpone_days" name="postpone_days" class="form-control" min="0" placeholder="Ingrese número de días" value="0">
        </div>
    </div>
</div>

<script>
    const periodsData = @json($periods);

    function formatearFecha(fecha) {
        const [anio, mes, dia] = fecha.split('-');
        return `${dia}/${mes}/${anio}`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const yearSelect = document.getElementById('year');
        const typeSelect = document.getElementById('type');
        const numSelect = document.getElementById('num');

        const typeGroup = document.getElementById('type-group');
        const numGroup = document.getElementById('num-group');

        yearSelect.addEventListener('change', () => {
            typeSelect.selectedIndex = 0;
            numSelect.innerHTML = `<option value="" disabled selected>Seleccione número</option>`;
            typeGroup.style.display = 'flex';
            numGroup.style.display = 'none';
        });

        typeSelect.addEventListener('change', () => {
            const year = yearSelect.value;
            const type = typeSelect.value;

            if (!year || !type || !periodsData[type] || !Array.isArray(periodsData[type][year])) {
                numGroup.style.display = 'none';
                return;
            }

            numSelect.innerHTML = `<option value="" disabled selected>Seleccione número</option>`;

            periodsData[type][year].forEach(period => {
                const option = document.createElement('option');
                option.value = period.id;

                if (type === 'week') {
                    const ini = formatearFecha(period.ini);
                    const fin = formatearFecha(period.fin);
                    option.textContent = `Semana ${period.num} (${ini} - ${fin})`;
                } else if (type === 'quincena') {
                    const corte = formatearFecha(period.ini);
                    option.textContent = `Quincena ${period.num} (${corte})`;
                }

                numSelect.appendChild(option);
            });

            numGroup.style.display = 'flex';
        });
    });
    const deactivateSelect = document.getElementById('deactivate');
    const postponeGroup = document.getElementById('postpone-group');

    deactivateSelect.addEventListener('change', () => {
        if (deactivateSelect.value === 'no') {
            postponeGroup.style.display = 'flex';
        } else {
            postponeGroup.style.display = 'none';
            document.getElementById('postpone_days').value = '';
        }
    });
</script>