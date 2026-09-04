<section class="page-header"><div><h1>Asignar cita</h1><p>Seleccione paciente, médico, consultorio, fecha y una hora disponible.</p></div></section>

<form class="panel form-grid" method="post" action="<?= e(url('/appointments')) ?>" id="appointment-form" novalidate>
    <?= csrf_field() ?>
    <div class="full-width">
        <label for="patient_document">Documento del paciente</label>
        <input id="patient_document" name="patient_document" value="<?= e($data['patient_document'] ?? '') ?>" required>
        <?php if (isset($errors['patient_document'])): ?><small class="field-error"><?= e($errors['patient_document']) ?> <a href="<?= e(url('/patients/create')) ?>">Registrar paciente</a></small><?php endif; ?>
    </div>

    <div>
        <label for="doctor_id">Médico</label>
        <select id="doctor_id" name="doctor_id" required>
            <option value="">Seleccione</option>
            <?php foreach ($doctors as $doctor): ?>
            <option value="<?= e($doctor['id']) ?>" <?= (int) ($data['doctor_id'] ?? 0) === (int) $doctor['id'] ? 'selected' : '' ?>>
                <?= e($doctor['first_name'] . ' ' . $doctor['last_name'] . ' - ' . $doctor['specialty']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['doctor_id'])): ?><small class="field-error"><?= e($errors['doctor_id']) ?></small><?php endif; ?>
    </div>  

    <div>
        <label for="room_id">Consultorio</label>
        <select id="room_id" name="room_id" required>
            <option value="">Seleccione</option>
                <?php foreach ($rooms as $room): ?>
            <option value="<?= e($room['id']) ?>" <?= (int) ($data['room_id'] ?? 0) === (int) $room['id'] ? 'selected' : '' ?>><?= e($room['code'] . ' - ' . $room['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['room_id'])): ?><small class="field-error"><?= e($errors['room_id']) ?></small><?php endif; ?>
    </div>

    <div>
        <label for="appointment_date">Fecha</label>
        <input id="appointment_date" name="appointment_date" type="date" min="<?= e($minimumDate) ?>" max="<?= e($maximumDate) ?>" value="<?= e($data['appointment_date'] ?? '') ?>" required>
        <?php if (isset($errors['appointment_date'])): ?><small class="field-error"><?= e($errors['appointment_date']) ?></small><?php endif; ?>
    </div>

    <div>
        <label for="appointment_time">Hora disponible</label>
        <select id="appointment_time" name="appointment_time" data-selected="<?= e($data['appointment_time'] ?? '') ?>" required>
            <option value="">Seleccione médico, consultorio y fecha</option>
        </select>
        <small id="availability-status" class="help" aria-live="polite"></small>
        <?php if (isset($errors['appointment_time'])): ?><small class="field-error"><?= e($errors['appointment_time']) ?></small><?php endif; ?>
    </div>

    <div class="full-width">
        <label for="notes">Observaciones</label>
        <textarea id="notes" name="notes" rows="4" maxlength="500"><?= e($data['notes'] ?? '') ?></textarea>
        <?php if (isset($errors['notes'])): ?><small class="field-error"><?= e($errors['notes']) ?></small><?php endif; ?>
    </div>

    <div class="form-actions full-width">
        <a class="button secondary" href="<?= e(url('/appointments')) ?>">Cancelar</a>
        <button class="button primary" type="submit">Asignar cita</button>
    </div>
    
</form>