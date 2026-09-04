document.addEventListener('DOMContentLoaded', () => {

    const configuredBase = document.body.dataset.baseUrl || '/';
    const normalizedBase = configuredBase === '/' ? '' : configuredBase.replace(/\/$/, '');
    const appUrl = (path) => `${normalizedBase}/${String(path).replace(/^\//, '')}`;

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || '¿Desea continuar?')) {
                event.preventDefault();
            }
        });
    });

    const doctor = document.querySelector('#doctor_id');
    const room = document.querySelector('#room_id');
    const date = document.querySelector('#appointment_date');
    const time = document.querySelector('#appointment_time');
    const status = document.querySelector('#availability-status');

    if (!doctor || !room || !date || !time || !status) return;
    let requestController = null;

    async function loadAvailability() {
        time.innerHTML = '<option value="">Seleccione médico, consultorio y fecha</option>';
        status.textContent = '';
        if (!doctor.value || !room.value || !date.value) return;
        if (requestController) requestController.abort();
        requestController = new AbortController();
        status.textContent = 'Consultando disponibilidad...';
        time.disabled = true;
        try {
            const params = new URLSearchParams({
                doctor_id: doctor.value,
                room_id: room.value,
                date: date.value,
            });

            const response = await fetch(`${appUrl('/api/availability')}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                signal: requestController.signal,
            });

            if (response.status === 401) {
                window.location.assign(appUrl('/login'));
                return;
            }

            if (!response.ok) throw new Error('No fue posible consultar la disponibilidad.');
            const data = await response.json();
            const selected = time.dataset.selected || '';
            time.innerHTML = '<option value="">Seleccione una hora</option>';
            data.slots.forEach((slot) => {
                const option = document.createElement('option');
                option.value = slot;
                option.textContent = slot.slice(0, 5);
                option.selected = selected === slot;
                time.append(option);
            });

            status.textContent = data.slots.length
            ? `${data.slots.length} horarios disponibles.`
            : 'No hay horarios disponibles para esta combinación.';
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = error.message;
            }
        } finally {
            time.disabled = false;
        }
    }

    [doctor, room, date].forEach((field) => field.addEventListener('change', loadAvailability));
    loadAvailability();

});