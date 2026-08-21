<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    
    <section class="page-header"><div><h1>Registrar paciente</h1><p>Complete los datos obligatorios.</p></div></section>
<form class="panel form-grid" method="post" action="<?= e(url('/patients')) ?>" novalidate>
<?= csrf_field() ?>
<div>
<label for="document_type">Tipo de documento</label>
<select id="document_type" name="document_type">
<?php foreach (['CC', 'TI', 'CE', 'PA'] as $type): ?>
<option value="<?= $type ?>" <?= ($data['document_type'] ?? 'CC') === $type ? 'selected' : '' ?>><?= $type ?></option>
<?php endforeach; ?>
</select>
<?php if (isset($errors['document_type'])): ?><small class="field-error"><?= e($errors['document_type']) ?></small><?php endif; ?>
</div>
<div>
<label for="document_number">Número de documento</label>
<input id="document_number" name="document_number" maxlength="30" value="<?= e($data['document_number'] ?? '') ?>" required>
<?php if (isset($errors['document_number'])): ?><small class="field-error"><?= e($errors['document_number']) ?></small><?php endif; ?>
</div>
<div>
<label for="first_name">Nombres</label>
<input id="first_name" name="first_name" maxlength="80" value="<?= e($data['first_name'] ?? '') ?>" required>
<?php if (isset($errors['first_name'])): ?><small class="field-error"><?= e($errors['first_name']) ?></small><?php endif; ?>
</div>
<div>
<label for="last_name">Apellidos</label>
<input id="last_name" name="last_name" maxlength="80" value="<?= e($data['last_name'] ?? '') ?>" required>
<?php if (isset($errors['last_name'])): ?><small class="field-error"><?= e($errors['last_name']) ?></small><?php endif; ?>
</div>
<div>
<label for="birth_date">Fecha de nacimiento</label>
<input id="birth_date" name="birth_date" type="date" max="<?= e(date('Y-m-d')) ?>" value="<?= e($data['birth_date'] ?? '') ?>" required>
<?php if (isset($errors['birth_date'])): ?><small class="field-error"><?= e($errors['birth_date']) ?></small><?php endif; ?>
</div>
<div>
<label for="sex">Sexo</label>
<select id="sex" name="sex" required>
<option value="">Seleccione</option>
<option value="F" <?= ($data['sex'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
<option value="M" <?= ($data['sex'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
<option value="O" <?= ($data['sex'] ?? '') === 'O' ? 'selected' : '' ?>>Otro / no informa</option>
</select>
<?php if (isset($errors['sex'])): ?><small class="field-error"><?= e($errors['sex']) ?></small><?php endif; ?>
</div>
  <div>
<label for="phone">Teléfono</label>
<input id="phone" name="phone" maxlength="30" value="<?= e($data['phone'] ?? '') ?>">
<?php if (isset($errors['phone'])): ?><small class="field-error"><?= e($errors['phone']) ?></small><?php endif; ?>
</div>
<div>
<label for="email">Correo</label>
<input id="email" name="email" type="email" maxlength="150" value="<?= e($data['email'] ?? '') ?>">
<?php if (isset($errors['email'])): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?>
</div>
<div class="form-actions full-width">
<a class="button secondary" href="<?= e(url('/patients')) ?>">Cancelar</a>
<button class="button primary" type="submit">Guardar paciente</button>
</div>
</form>

</body>
</html>