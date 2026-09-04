<section class="page-header">
    <div>
        <h1>Registrar Médico</h1>
        <p>Complete los datos obligatorios.</p>
    </div>
</section>

<form class="panel form-grid" method="post" action="<?= e(url('/doctors')) ?>" novalidate>
    <?= csrf_field() ?>

    <div>
        <label for="license_number">Numero de Licencia</label>
        <input id="license_number" name="license_number" maxlength="30" value="<?= e($data['license_number'] ?? '') ?>">
        <?php if (isset($errors['license_number'])): ?><small class="field-error"><?= e($errors['license_number']) ?></small><?php endif; ?>
    </div>
    
    <div>
        <label for="first_name">Nombres</label>
        <input id="first_name" name="first_name" maxlength="80" value="<?= e($data['first_name'] ?? '') ?>" required>
        <?php if (isset($errors['first_name'])): ?>
        <small class="field-error">
            <?= e($errors['first_name']) ?>
        </small>
        <?php endif; ?>
    </div>
    
    <div>
        <label for="last_name">Apellidos</label>
        <input id="last_name" name="last_name" maxlength="80" value="<?= e($data['last_name'] ?? '') ?>" required>
        <?php if (isset($errors['last_name'])): ?>
        <small class="field-error">
            <?= e($errors['last_name']) ?>
        </small>
        <?php endif; ?>
    </div>

    <div>
        <label for="specialty">Especialidad</label>
        <select id="specialty" name="specialty" required>
            <option value="">Seleccione</option>
            <option value="Medicina General" <?= ($data['specialty'] ?? '') === 'Medicina General' ? 'selected' : '' ?>>Medicina General</option>
            <option value="Cardiologia" <?= ($data['specialty'] ?? '') === 'Cardiologia' ? 'selected' : '' ?>>Cardiologia</option>
            <option value="Pediatria" <?= ($data['specialty'] ?? '') === 'Pediatria' ? 'selected' : '' ?>>Pediatria</option>
        </select>
        <?php if (isset($errors['specialty'])): ?><small class="field-error"><?= e($errors['specialty']) ?></small><?php endif; ?>
    </div>

    <div class="form-actions full-width">
        <label for="active">Estado</label>
        <select id="active" name="active" required>
            <option value="">Seleccione</option>
            <option value="1" <?= ($data['active'] ?? '') === '1' ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= ($data['inactive'] ?? '') === '0' ? 'selected' : '' ?>>Inactivo</option>
        </select>
        <?php if (isset($errors['active'])): ?><small class="field-error"><?= e($errors['active']) ?></small><?php endif; ?>
    </div>
    
    <div class="form-actions full-width">
        <a class="button secondary" href="<?= e(url('/doctors')) ?>">Cancelar</a>
        <button class="button primary" type="submit">Guardar Médico</button>
    </div>
</form>