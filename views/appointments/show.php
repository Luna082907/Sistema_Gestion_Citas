<?php
use App\Core\Auth;

$canCancel = Auth::hasRole(Auth::ROLE_ADMIN, Auth::ROLE_RECEPTIONIST);
$canClose  = Auth::hasRole(Auth::ROLE_ADMIN, Auth::ROLE_RECEPTIONIST, Auth::ROLE_DOCTOR);
?>

<section class="page-header">
    <div><h1>Cita #<?= e($appointment['id']) ?></h1><p>Información completa de la cita.</p></div>
    <a class="button secondary" href="<?= e(url('/appointments')) ?>">Volver</a>
</section>

<div class="detail-grid panel">
<div><span>Paciente</span><strong><?= e($appointment['patient_first_name'] . ' ' . $appointment['patient_last_name']) ?></strong><small><?= e($appointment['document_type'] . ' ' . $appointment['document_number']) ?></small></div>
<div><span>Médico</span><strong><?= e($appointment['doctor_first_name'] . ' ' . $appointment['doctor_last_name']) ?></strong><small><?= e($appointment['specialty']) ?></small></div>
<div><span>Fecha</span><strong><?= e(format_date($appointment['appointment_date'])) ?></strong></div>
<div><span>Hora</span><strong><?= e(format_time($appointment['appointment_time'])) ?></strong></div>
<div><span>Consultorio</span><strong><?= e($appointment['room_code'] . ' - ' . $appointment['room_name']) ?></strong></div>
<div><span>Estado</span><strong class="status <?= e($appointment['status']) ?>"><?= e(appointment_status_label($appointment['status'])) ?></strong></div>
<div class="full-width"><span>Observaciones</span><strong><?= e($appointment['notes'] ?: 'Sin observaciones') ?></strong></div>
<div><span>Creada por</span><strong><?= e($appointment['creator_name']) ?></strong><small><?= e($appointment['created_at']) ?></small></div>
<?php if ($appointment['cancelled_by']): ?><div><span>Cancelada por</span><strong><?= e($appointment['canceller_name']) ?></strong><small><?= e($appointment['cancelled_at']) ?></small></div><?php endif; ?>
<?php if ($appointment['completed_by']): ?><div><span>Atendida por</span><strong><?= e($appointment['completer_name']) ?></strong><small><?= e($appointment['completed_at']) ?></small></div><?php endif; ?>
</div>
<?php if ($appointment['status'] === 'scheduled'): ?>
<?php $appointmentMoment = new DateTimeImmutable($appointment['appointment_date'] . ' ' . $appointment['appointment_time']); ?>
<div class="actions">
<?php if ($canClose && $appointmentMoment <= new DateTimeImmutable('now')): ?>

<form method="post" action="<?= e(url('/appointments/' . $appointment['id'] . '/complete')) ?>" data-confirm="¿Confirma que la cita fue atendida?">
    <?= csrf_field() ?>
    <button class="button success" type="submit">Marcar atendida</button>
</form>

<?php endif; ?>

<?php if ($canCancel): ?>
<form method="post" action="<?= e(url('/appointments/' . $appointment['id'] . '/cancel')) ?>" data-confirm="¿Está seguro de cancelar esta cita?">
    <?= csrf_field() ?>
    <button class="button danger" type="submit">Cancelar cita</button>
</form>
<?php endif; ?>

</div>
<?php endif; ?>