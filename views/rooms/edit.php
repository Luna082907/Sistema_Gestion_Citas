<section class="page-header">
    <div>
        <h1>Editar Consultorio</h1>
        <p>Actualice los datos del consultorio.</p>
    </div>
</section>

<form class="panel form-grid" method="post" action="<?= e(url('/rooms/' . ($data['id'] ?? $_GET['id'] ?? ''))) ?>" novalidate>
    <?= csrf_field() ?>

    <div>
        <label for="code">Código</label>
        <input id="code" name="code" maxlength="20" value="<?= e($data['code'] ?? '') ?>" required>
        <?php if (isset($errors['code'])): ?><small class="field-error"><?= e($errors['code']) ?></small><?php endif; ?>
    </div>

    <div>
        <label for="name">Nombre</label>
        <input id="name" name="name" maxlength="80" value="<?= e($data['name'] ?? '') ?>" required>
        <?php if (isset($errors['name'])): ?><small class="field-error"><?= e($errors['name']) ?></small><?php endif; ?>
    </div>

    <div>
        <label for="active">Estado</label>
        <select id="active" name="active" required>
            <option value="">Seleccione</option>
            <option value="1" <?= ($data['active'] ?? '') === '1' ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= ($data['active'] ?? '') === '0' ? 'selected' : '' ?>>Inactivo</option>
        </select>
        <?php if (isset($errors['active'])): ?><small class="field-error"><?= e($errors['active']) ?></small><?php endif; ?>
    </div>

    <div class="form-actions full-width">
        <a class="button secondary" href="<?= e(url('/rooms')) ?>">Cancelar</a>
        <button class="button primary" type="submit">Actualizar consultorio</button>
    </div>
</form>